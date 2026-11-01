<?php

namespace Tests\Feature;

use App\Models\CounselingService;
use App\Models\StudentOrganization;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StudentLifeTest extends TestCase
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

    public function test_public_student_organizations_page_shows_osis_and_mpk(): void
    {
        $osis = StudentOrganization::where('short_name', 'OSIS')->firstOrFail();

        $this->assertNotNull($osis->photo_path);
        Storage::disk('public')->assertExists($osis->photo_path);

        $this->get(route('student-organizations'))
            ->assertOk()
            ->assertSee('Organisasi Siswa')
            ->assertSee('Organisasi Siswa Intra Sekolah')
            ->assertSee('Majelis Perwakilan Kelas')
            ->assertSee('Rina Kartika, S.Pd.')
            ->assertSee('Dedi Kurniawan, S.Pd.')
            ->assertSee('Terwujudnya siswa yang aktif')
            ->assertSee('Menjadi jembatan komunikasi')
            ->assertSee('Latihan Dasar Kepemimpinan Siswa')
            ->assertSee('Forum Aspirasi Siswa')
            ->assertSee($osis->photoUrl(), false);
    }

    public function test_public_counseling_page_shows_services_and_character_programs(): void
    {
        $this->get(route('counseling'))
            ->assertOk()
            ->assertSee('Bimbingan Konseling')
            ->assertSee('Layanan konsultasi siswa')
            ->assertSee('Konsultasi Pribadi &amp; Sosial', false)
            ->assertSee('Konsultasi Karier')
            ->assertSee('Senin–Jumat, 08.00–14.00 WIB')
            ->assertSee('guru.bk@sekolah.id')
            ->assertSee('Program pembinaan siswa')
            ->assertSee('Pembinaan Karakter Mingguan')
            ->assertSee('Program Anti-Perundungan')
            ->assertSee('Mentoring Sebaya');
    }

    public function test_public_navigation_uses_kesiswaan_menu_and_places_prestasi_after_news(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Kesiswaan &amp; Ekstrakurikuler', false)
            ->assertSee('href="'.route('extracurriculars').'"', false)
            ->assertSee('href="'.route('student-organizations').'"', false)
            ->assertSee('href="'.route('counseling').'"', false)
            ->assertSeeInOrder([
                'href="'.route('news.index').'"',
                'href="'.route('achievements.academic').'"',
                'href="'.route('achievements.non_academic').'"',
            ], false);
    }

    public function test_inactive_student_life_content_is_hidden_from_public_pages(): void
    {
        StudentOrganization::create(['name' => 'Organisasi Rahasia', 'short_name' => 'OR', 'is_active' => false]);
        CounselingService::create(['category' => 'layanan', 'title' => 'Layanan Rahasia', 'is_active' => false]);

        $this->get(route('student-organizations'))->assertOk()->assertDontSee('Organisasi Rahasia');
        $this->get(route('counseling'))->assertOk()->assertDontSee('Layanan Rahasia');

        $this->actingAs($this->admin())
            ->get(route('admin.student-organizations.index'))
            ->assertOk()
            ->assertSee('Organisasi Rahasia');

        $this->actingAs($this->admin())
            ->get(route('admin.counseling-services.index'))
            ->assertOk()
            ->assertSee('Layanan Rahasia');
    }

    public function test_admin_manages_student_organization_photo(): void
    {
        $this->actingAs($this->admin());

        $this->get(route('admin.student-organizations.create'))->assertOk()->assertSee('Tambah Organisasi Siswa');

        $this->post(route('admin.student-organizations.store'), [
            'name' => 'Dewan Ambalan',
            'short_name' => 'DA',
            'advisor' => 'Ahmad Fauzi, S.Pd.',
            'description' => 'Organisasi kepramukaan penegak yang membina kedisiplinan dan kepemimpinan.',
            'vision' => 'Terbentuknya anggota yang mandiri, disiplin, dan peduli lingkungan.',
            'mission' => "Menyelenggarakan latihan kepramukaan rutin.\nMengembangkan jiwa kepemimpinan dan pelayanan.",
            'programs' => "Persami\nBakti Lingkungan\nLomba Pramuka",
            'sort_order' => 3,
            'is_active' => 1,
            'photo' => UploadedFile::fake()->image('dewan-ambalan.jpg', 800, 600),
        ])->assertRedirect(route('admin.student-organizations.index'));

        $organization = StudentOrganization::where('short_name', 'DA')->firstOrFail();
        $photoPath = $organization->photo_path;

        $this->assertNotNull($photoPath);
        Storage::disk('public')->assertExists($photoPath);
        $this->get('/media/'.$photoPath)->assertOk();

        $this->assertSame(['Menyelenggarakan latihan kepramukaan rutin.', 'Mengembangkan jiwa kepemimpinan dan pelayanan.'], $organization->missionList());

        $this->get(route('admin.student-organizations.index'))->assertOk()->assertSee('Dewan Ambalan');
        $this->get(route('admin.student-organizations.edit', $organization))->assertOk()->assertSee('Dewan Ambalan');
        $this->get(route('student-organizations'))->assertOk()->assertSee('Dewan Ambalan');

        $this->put(route('admin.student-organizations.update', $organization), [
            'name' => 'Dewan Ambalan',
            'short_name' => 'DA',
            'remove_photo' => 1,
            'is_active' => 1,
        ])->assertRedirect(route('admin.student-organizations.index'));

        $organization->refresh();
        $this->assertNull($organization->photo_path);
        Storage::disk('public')->assertMissing($photoPath);

        $this->delete(route('admin.student-organizations.destroy', $organization))
            ->assertRedirect(route('admin.student-organizations.index'));
        $this->assertDatabaseMissing('student_organizations', ['id' => $organization->id]);
    }

    public function test_admin_manages_counseling_service(): void
    {
        $this->actingAs($this->admin());

        $this->get(route('admin.counseling-services.create'))->assertOk()->assertSee('Tambah Layanan BK');

        $this->post(route('admin.counseling-services.store'), [
            'category' => 'layanan',
            'title' => 'Konsultasi Minat Bakat',
            'description' => 'Pendampingan mengenali minat dan bakat siswa.',
            'schedule' => 'Rabu, 09.00–12.00 WIB',
            'contact' => 'Ruang BK · guru.bk@sekolah.id',
            'sort_order' => 9,
            'is_active' => 1,
        ])->assertRedirect(route('admin.counseling-services.index'));

        $service = CounselingService::where('title', 'Konsultasi Minat Bakat')->firstOrFail();

        $this->get(route('admin.counseling-services.index'))->assertOk()->assertSee('Konsultasi Minat Bakat');
        $this->get(route('admin.counseling-services.edit', $service))->assertOk()->assertSee('Konsultasi Minat Bakat');
        $this->get(route('counseling'))->assertOk()->assertSee('Konsultasi Minat Bakat');

        $this->post(route('admin.counseling-services.store'), [
            'category' => 'lainnya',
            'title' => 'Layanan Tidak Valid',
            'is_active' => 1,
        ])->assertSessionHasErrors('category');

        $this->put(route('admin.counseling-services.update', $service), [
            'category' => 'program',
            'title' => 'Konsultasi Minat Bakat',
            'description' => 'Pendampingan mengenali minat dan bakat siswa.',
            'is_active' => 1,
        ])->assertRedirect(route('admin.counseling-services.index'));

        $this->assertSame('program', $service->fresh()->category);

        $this->delete(route('admin.counseling-services.destroy', $service))
            ->assertRedirect(route('admin.counseling-services.index'));
        $this->assertDatabaseMissing('counseling_services', ['id' => $service->id]);
    }
}
