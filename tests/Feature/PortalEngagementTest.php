<?php

namespace Tests\Feature;

use App\Models\ContactMessage;
use App\Models\StudentWriting;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PortalEngagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        $this->seed(DatabaseSeeder::class);
    }

    private function admin(): User
    {
        return User::where('email', 'admin@sekolah.id')->firstOrFail();
    }

    public function test_contact_page_shows_social_links_and_message_form(): void
    {
        $this->get(route('contact'))
            ->assertOk()
            ->assertSee('Media Sosial Resmi')
            ->assertSee('https://www.instagram.com/edulinkschool')
            ->assertSee('https://www.youtube.com/@edulinkschool')
            ->assertSee('https://www.facebook.com/edulinkschool')
            ->assertSee('https://www.tiktok.com/@edulinkschool')
            ->assertSee('Kirim Pesan / Saran')
            ->assertSee('id="contact_message"', false);
    }

    public function test_contact_form_stores_message_and_admin_marks_it_read(): void
    {
        $this->from(route('contact'))->post(route('contact.send'), [
            'name' => 'Wali Murid',
            'email' => 'wali@example.com',
            'phone' => '0812-0000-1111',
            'subject' => 'Pertanyaan PPDB',
            'message' => 'Kapan pendaftaran peserta didik baru dibuka?',
        ])->assertRedirect(route('contact').'#kirim-pesan')
            ->assertSessionHas('success');

        $message = ContactMessage::where('email', 'wali@example.com')->firstOrFail();

        $this->assertSame('Wali Murid', $message->name);
        $this->assertFalse($message->is_read);

        $this->actingAs($this->admin())
            ->get(route('admin.contact-messages.index'))
            ->assertOk()
            ->assertSee('Pertanyaan PPDB')
            ->assertSee('Belum dibaca');

        $this->actingAs($this->admin())
            ->get(route('admin.contact-messages.show', $message))
            ->assertOk()
            ->assertSee('Kapan pendaftaran peserta didik baru dibuka?')
            ->assertSee('Balas via Email');

        $this->assertTrue($message->fresh()->is_read);

        $this->actingAs($this->admin())
            ->delete(route('admin.contact-messages.destroy', $message))
            ->assertRedirect(route('admin.contact-messages.index'));

        $this->assertDatabaseMissing('contact_messages', ['id' => $message->id]);
    }

    public function test_contact_form_requires_valid_fields(): void
    {
        $this->from(route('contact'))
            ->post(route('contact.send'), [])
            ->assertSessionHasErrors(['name', 'email', 'subject', 'message']);

        $this->assertDatabaseMissing('contact_messages', ['email' => 'wali@example.com']);
    }

    public function test_demo_contact_messages_fill_the_admin_inbox(): void
    {
        $this->assertGreaterThanOrEqual(6, ContactMessage::count());
        $this->assertGreaterThan(0, ContactMessage::unread()->count());
        $this->assertGreaterThan(0, ContactMessage::where('is_read', true)->count());

        $this->actingAs($this->admin())
            ->get(route('admin.contact-messages.index'))
            ->assertOk()
            ->assertSee('Pendaftaran Peserta Didik Baru 2027/2028')
            ->assertSee('Kerja Sama Program Magang Siswa')
            ->assertSee('Belum dibaca');
    }

    public function test_admin_updates_social_links_used_by_contact_page(): void
    {
        $this->actingAs($this->admin())
            ->get(route('admin.settings.edit'))
            ->assertOk()
            ->assertSee('Media Sosial')
            ->assertSee('name="social_instagram"', false);

        $this->actingAs($this->admin())
            ->put(route('admin.settings.update'), [
                'school_name' => 'SMA Edulink School',
                'school_short' => 'Edulink School',
                'social_instagram' => 'https://instagram.com/sekolahbaru',
                'social_facebook' => 'https://facebook.com/sekolahbaru',
                'social_youtube' => '',
                'social_tiktok' => '',
            ])->assertRedirect(route('admin.settings.edit'));

        $this->get(route('contact'))
            ->assertOk()
            ->assertSee('https://instagram.com/sekolahbaru')
            ->assertSee('https://facebook.com/sekolahbaru')
            ->assertDontSee('https://www.instagram.com/edulinkschool')
            ->assertDontSee('https://www.youtube.com/@edulinkschool')
            ->assertDontSee('https://www.tiktok.com/@edulinkschool');
    }

    public function test_topbar_search_finds_news_and_student_writings(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('class="topbar-search"', false)
            ->assertSee('action="'.route('search').'"', false);

        $this->get(route('search', ['q' => 'OSN']))
            ->assertOk()
            ->assertSee('hasil untuk')
            ->assertSee('Medali Emas OSN Informatika');

        $this->get(route('search', ['q' => 'sepeda']))
            ->assertOk()
            ->assertSee('Sepeda Tua dan Langit Sore di Ujung Jalan')
            ->assertSee('Majalah / Artikel Siswa');

        $this->get(route('search'))
            ->assertOk()
            ->assertSee('Pencarian Cepat')
            ->assertSee('Masukkan minimal dua karakter');
    }

    public function test_writings_pages_show_seeded_articles_and_filter_by_category(): void
    {
        $cerpen = StudentWriting::where('category', 'cerpen')->firstOrFail();

        $this->get(route('writings.index'))
            ->assertOk()
            ->assertSee('Majalah / Artikel Siswa')
            ->assertSee('Menumbuhkan Budaya Literasi di Ruang Kelas')
            ->assertSee('Sepeda Tua dan Langit Sore di Ujung Jalan')
            ->assertSee($cerpen->photoUrl(), false);

        $this->get(route('writings.show', $cerpen))
            ->assertOk()
            ->assertSee('Rizky Pratama')
            ->assertSee('Cerpen')
            ->assertSee('Kembali ke Majalah / Artikel Siswa');

        $this->get(route('writings.index', ['kategori' => 'opini']))
            ->assertOk()
            ->assertSee('Belajar Bukan Perlombaan: Catatan Seorang Siswa')
            ->assertDontSee('Sepeda Tua dan Langit Sore di Ujung Jalan');
    }

    public function test_draft_writings_are_hidden_from_public_but_visible_in_admin(): void
    {
        $draft = StudentWriting::create([
            'title' => 'Karya Tersembunyi',
            'author_name' => 'Penulis Draf',
            'author_type' => 'siswa',
            'category' => 'artikel',
            'body' => 'Isi karya yang belum diterbitkan.',
            'is_published' => false,
        ]);

        $this->get(route('writings.index'))->assertOk()->assertDontSee('Karya Tersembunyi');
        $this->get(route('writings.show', $draft))->assertNotFound();

        $this->actingAs($this->admin())
            ->get(route('admin.writings.index'))
            ->assertOk()
            ->assertSee('Karya Tersembunyi');
    }

    public function test_admin_creates_and_updates_writing_with_photo(): void
    {
        $this->actingAs($this->admin())
            ->get(route('admin.writings.create'))
            ->assertOk()
            ->assertSee('Tulis Artikel Siswa');

        $this->actingAs($this->admin())
            ->post(route('admin.writings.store'), [
                'title' => 'Puisi untuk Ibu',
                'author_name' => 'Salsa Puspita',
                'author_type' => 'siswa',
                'category' => 'opini',
                'excerpt' => 'Catatan kecil tentang ibu.',
                'body' => 'Ibu adalah rumah yang selalu terbuka.',
                'is_published' => 1,
                'photo' => UploadedFile::fake()->image('sampul.jpg', 800, 600),
            ])->assertRedirect(route('admin.writings.index'));

        $writing = StudentWriting::where('slug', 'puisi-untuk-ibu')->firstOrFail();

        $this->assertSame('Salsa Puspita', $writing->author_name);
        $this->assertTrue($writing->is_published);
        $this->assertNotNull($writing->published_at);
        Storage::disk('public')->assertExists($writing->photo_path);

        $oldPhoto = $writing->photo_path;

        $this->actingAs($this->admin())
            ->put(route('admin.writings.update', $writing), [
                'title' => 'Puisi untuk Ibu',
                'author_name' => 'Salsa Puspita',
                'author_type' => 'siswa',
                'category' => 'opini',
                'excerpt' => 'Catatan kecil tentang ibu.',
                'body' => 'Ibu adalah rumah yang selalu terbuka, versi yang diperbarui.',
                'is_published' => 1,
                'remove_photo' => 1,
            ])->assertRedirect(route('admin.writings.index'));

        $this->assertNull($writing->fresh()->photo_path);
        Storage::disk('public')->assertMissing($oldPhoto);

        $this->actingAs($this->admin())
            ->delete(route('admin.writings.destroy', $writing))
            ->assertRedirect(route('admin.writings.index'));

        $this->assertDatabaseMissing('student_writings', ['id' => $writing->id]);
    }

    public function test_news_menu_links_to_majalah(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('href="'.route('writings.index').'"', false);

        $this->get(route('news.index'))
            ->assertOk()
            ->assertSee('href="'.route('writings.index').'"', false);
    }

    public function test_prestasi_links_live_inside_akademik_menu(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSeeInOrder([
                route('curriculum'),
                route('academic-calendar'),
                route('alumni'),
                route('achievements.academic'),
                route('achievements.non_academic'),
                route('extracurriculars'),
            ], false);
    }

    public function test_public_layout_exposes_accent_presets_and_theme_modes(): void
    {
        $this->assertFileExists(public_path('js/theme.js'));

        $css = file_get_contents(public_path('css/app.css'));
        $this->assertStringContainsString('html[data-theme="dark"]', $css);
        $this->assertStringContainsString('html[data-accent="botanical"]', $css);
        $this->assertStringContainsString('html[data-accent="karamel"]', $css);
        $this->assertStringContainsString('html[data-accent="ungu"]', $css);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('js/theme.js', false)
            ->assertSee('data-theme-picker', false)
            ->assertSee('data-theme-panel', false)
            ->assertSee('Biru Edukasi')
            ->assertSee('Hijau Marun')
            ->assertSee('Merah Karamel')
            ->assertSee('Ungu Modern')
            ->assertSee('Toska Samudra')
            ->assertSee('data-accent-choice="custom"', false)
            ->assertSee('data-theme-color', false)
            ->assertSee('data-mode-choice="light"', false)
            ->assertSee('data-mode-choice="dark"', false)
            ->assertSee('data-mode-choice="system"', false);
    }
}
