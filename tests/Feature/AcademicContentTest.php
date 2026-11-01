<?php

namespace Tests\Feature;

use App\Models\AcademicEvent;
use App\Models\Achievement;
use App\Models\AlumniDestination;
use App\Models\CurriculumGroup;
use App\Models\Setting;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AcademicContentTest extends TestCase
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

    public function test_public_achievement_pages_split_academic_and_non_academic(): void
    {
        $this->get(route('achievements.academic'))
            ->assertOk()
            ->assertSee('Prestasi Akademik')
            ->assertSee('Medali Emas OSN Informatika 2026')
            ->assertDontSee('Juara 2 Kompetisi Robotik Nasional')
            ->assertSee('Nasional');

        $this->get(route('achievements.non_academic'))
            ->assertOk()
            ->assertSee('Prestasi Non Akademik')
            ->assertSee('Juara 2 Kompetisi Robotik Nasional')
            ->assertDontSee('Medali Emas OSN Informatika 2026');
    }

    public function test_public_curriculum_page_shows_curriculum_and_groups(): void
    {
        $this->get(route('curriculum'))
            ->assertOk()
            ->assertSee('Kurikulum Merdeka')
            ->assertSee('Kelompok Mata Pelajaran Wajib')
            ->assertSee('Peminatan Matematika dan Sains (MIPA)')
            ->assertSee('Fisika')
            ->assertSee('Sosiologi');
    }

    public function test_public_academic_calendar_groups_events_by_month(): void
    {
        $this->get(route('academic-calendar'))
            ->assertOk()
            ->assertSee('Kalender Akademik')
            ->assertSee('Penilaian Tengah Semester Ganjil')
            ->assertSee('Libur Semester Ganjil')
            ->assertSee('September 2026')
            ->assertSee('Desember 2026')
            ->assertSee('Ujian');
    }

    public function test_public_alumni_page_shows_distribution_and_stats(): void
    {
        $this->get(route('alumni'))
            ->assertOk()
            ->assertSee('Info PTN / Alumni')
            ->assertSee('Institut Teknologi Bandung')
            ->assertSee('Universitas Indonesia')
            ->assertSee('Diterima di PTN')
            ->assertSee('Lulus tahun 2025')
            ->assertSee('media/photos/demo/alumni-ananda-putri.jpg', false);
    }

    public function test_public_navigation_includes_profile_facilities_and_new_dropdowns(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('href="'.route('facilities').'"', false)
            ->assertSee('href="'.route('achievements.academic').'"', false)
            ->assertSee('href="'.route('achievements.non_academic').'"', false)
            ->assertSee('href="'.route('curriculum').'"', false)
            ->assertSee('href="'.route('academic-calendar').'"', false)
            ->assertSee('href="'.route('alumni').'"', false)
            ->assertSee('Profil Sekolah')
            ->assertSee('Prestasi')
            ->assertSee('Akademik');
    }

    public function test_inactive_academic_content_is_hidden_from_public_pages(): void
    {
        Achievement::create(['category' => 'akademik', 'title' => 'Prestasi Rahasia', 'is_active' => false]);
        CurriculumGroup::create(['name' => 'Kelompok Rahasia', 'is_active' => false]);
        AcademicEvent::create(['title' => 'Agenda Rahasia', 'category' => 'libur', 'starts_at' => '2026-11-01', 'is_active' => false]);
        AlumniDestination::create([
            'name' => 'Alumni Rahasia',
            'university' => 'Universitas Rahasia',
            'graduation_year' => 2020,
            'type' => 'ptn',
            'is_active' => false,
        ]);

        $this->get(route('achievements.academic'))->assertOk()->assertDontSee('Prestasi Rahasia');
        $this->get(route('curriculum'))->assertOk()->assertDontSee('Kelompok Rahasia');
        $this->get(route('academic-calendar'))->assertOk()->assertDontSee('Agenda Rahasia');
        $this->get(route('alumni'))->assertOk()->assertDontSee('Alumni Rahasia');

        $this->actingAs($this->admin())
            ->get(route('admin.achievements.index'))
            ->assertOk()
            ->assertSee('Prestasi Rahasia');
    }

    public function test_admin_manages_achievement_photo(): void
    {
        $this->actingAs($this->admin());

        $this->get(route('admin.achievements.create'))->assertOk()->assertSee('Tambah Prestasi');

        $this->post(route('admin.achievements.store'), [
            'category' => 'akademik',
            'title' => 'Juara Debat Bahasa Inggris',
            'recipient' => 'Tim Debat Nusantara',
            'level' => 'Provinsi',
            'achieved_at' => '2026-10-01',
            'description' => 'Tim debat meraih juara pertama pada kompetisi antar sekolah.',
            'sort_order' => 9,
            'is_active' => 1,
            'photo' => UploadedFile::fake()->image('debat.jpg', 800, 600),
        ])->assertRedirect(route('admin.achievements.index'));

        $achievement = Achievement::where('title', 'Juara Debat Bahasa Inggris')->firstOrFail();
        $photoPath = $achievement->photo_path;

        $this->assertNotNull($photoPath);
        Storage::disk('public')->assertExists($photoPath);
        $this->get('/media/'.$photoPath)->assertOk();

        $this->get(route('admin.achievements.index'))->assertOk()->assertSee('Juara Debat Bahasa Inggris');
        $this->get(route('admin.achievements.edit', $achievement))->assertOk()->assertSee('Juara Debat Bahasa Inggris');
        $this->get(route('achievements.academic'))->assertOk()->assertSee('Juara Debat Bahasa Inggris');

        $this->put(route('admin.achievements.update', $achievement), [
            'category' => 'non_akademik',
            'title' => 'Juara Debat Bahasa Inggris',
            'is_active' => 1,
            'remove_photo' => 1,
        ])->assertRedirect(route('admin.achievements.index'));

        $achievement->refresh();
        $this->assertSame('non_akademik', $achievement->category);
        $this->assertNull($achievement->photo_path);
        Storage::disk('public')->assertMissing($photoPath);

        $this->delete(route('admin.achievements.destroy', $achievement))
            ->assertRedirect(route('admin.achievements.index'));
        $this->assertDatabaseMissing('achievements', ['id' => $achievement->id]);
    }

    public function test_admin_manages_curriculum_group(): void
    {
        $this->actingAs($this->admin());

        $this->get(route('admin.curriculum-groups.create'))->assertOk()->assertSee('Tambah Kelompok Kurikulum');

        $this->post(route('admin.curriculum-groups.store'), [
            'name' => 'Peminatan Kewirausahaan',
            'description' => 'Kelompok untuk siswa yang berminat pada bisnis dan kewirausahaan.',
            'subjects' => "Ekonomi Lanjut\nPrakarya dan Kewirausahaan",
            'sort_order' => 5,
            'is_active' => 1,
        ])->assertRedirect(route('admin.curriculum-groups.index'));

        $group = CurriculumGroup::where('name', 'Peminatan Kewirausahaan')->firstOrFail();
        $this->assertSame(['Ekonomi Lanjut', 'Prakarya dan Kewirausahaan'], $group->subjectList());

        $this->get(route('admin.curriculum-groups.edit', $group))->assertOk()->assertSee('Peminatan Kewirausahaan');

        $this->put(route('admin.curriculum-groups.update', $group), [
            'name' => 'Peminatan Bisnis Digital',
            'subjects' => 'Ekonomi Lanjut',
            'is_active' => 1,
        ])->assertRedirect(route('admin.curriculum-groups.index'));

        $this->get(route('curriculum'))->assertOk()->assertSee('Peminatan Bisnis Digital');

        $this->delete(route('admin.curriculum-groups.destroy', $group))
            ->assertRedirect(route('admin.curriculum-groups.index'));
        $this->assertDatabaseMissing('curriculum_groups', ['id' => $group->id]);
    }

    public function test_admin_manages_academic_event_and_validates_date_range(): void
    {
        $this->actingAs($this->admin());

        $this->get(route('admin.academic-events.create'))->assertOk()->assertSee('Tambah Agenda Akademik');

        $this->post(route('admin.academic-events.store'), [
            'title' => 'Gelar Karya Projek',
            'category' => 'kegiatan',
            'starts_at' => '2026-11-10',
            'ends_at' => '2026-11-12',
            'description' => 'Pameran hasil projek penguatan profil pelajar Pancasila.',
            'is_active' => 1,
        ])->assertRedirect(route('admin.academic-events.index'));

        $event = AcademicEvent::where('title', 'Gelar Karya Projek')->firstOrFail();
        $this->get(route('admin.academic-events.edit', $event))->assertOk()->assertSee('Gelar Karya Projek');
        $this->get(route('academic-calendar'))->assertOk()->assertSee('Gelar Karya Projek');

        $this->post(route('admin.academic-events.store'), [
            'title' => 'Agenda Tidak Valid',
            'category' => 'ujian',
            'starts_at' => '2026-11-10',
            'ends_at' => '2026-11-01',
            'is_active' => 1,
        ])->assertSessionHasErrors('ends_at');

        $this->delete(route('admin.academic-events.destroy', $event))
            ->assertRedirect(route('admin.academic-events.index'));
        $this->assertDatabaseMissing('academic_events', ['id' => $event->id]);
    }

    public function test_admin_manages_alumni_destination(): void
    {
        $this->actingAs($this->admin());

        $this->get(route('admin.alumni.create'))->assertOk()->assertSee('Tambah Data Alumni');

        $this->post(route('admin.alumni.store'), [
            'name' => 'Aulia Rahmadani',
            'university' => 'Institut Teknologi Bandung',
            'major' => 'Arsitektur',
            'graduation_year' => 2026,
            'type' => 'ptn',
            'sort_order' => 3,
            'is_active' => 1,
            'photo' => UploadedFile::fake()->image('aulia.jpg', 400, 400),
        ])->assertRedirect(route('admin.alumni.index'));

        $alumni = AlumniDestination::where('name', 'Aulia Rahmadani')->firstOrFail();
        $photoPath = $alumni->photo_path;

        $this->assertNotNull($photoPath);
        $this->assertStringStartsWith('photos/alumni/', $photoPath);
        Storage::disk('public')->assertExists($photoPath);

        $this->get(route('admin.alumni.edit', $alumni))
            ->assertOk()
            ->assertSee('Aulia Rahmadani')
            ->assertSee('media/'.$photoPath, false);
        $this->get(route('alumni'))
            ->assertOk()
            ->assertSee('Aulia Rahmadani')
            ->assertSee('media/'.$photoPath, false)
            ->assertSee('Foto Aulia Rahmadani', false);

        $this->put(route('admin.alumni.update', $alumni), [
            'name' => 'Aulia Rahmadani',
            'university' => 'Universitas Indonesia',
            'major' => 'Arsitektur',
            'graduation_year' => 2026,
            'type' => 'ptn',
            'is_active' => 1,
            'remove_photo' => 1,
        ])->assertRedirect(route('admin.alumni.index'));

        $alumni->refresh();

        $this->assertSame('Universitas Indonesia', $alumni->university);
        $this->assertNull($alumni->photo_path);
        Storage::disk('public')->assertMissing($photoPath);
        $this->get(route('alumni'))->assertOk()->assertDontSee('media/'.$photoPath, false);

        $this->delete(route('admin.alumni.destroy', $alumni))
            ->assertRedirect(route('admin.alumni.index'));
        $this->assertDatabaseMissing('alumni_destinations', ['id' => $alumni->id]);
    }

    public function test_admin_saves_curriculum_settings(): void
    {
        $this->actingAs($this->admin());

        $this->get(route('admin.settings.edit'))->assertOk()->assertSee('Kurikulum');

        $this->put(route('admin.settings.update'), [
            'school_name' => 'SMA Edulink School',
            'school_short' => 'Edulink School',
            'curriculum_name' => 'Kurikulum Merdeka Belajar',
            'curriculum_description' => 'Pembelajaran berbasis projek dengan penguatan profil pelajar Pancasila.',
        ])->assertRedirect(route('admin.settings.edit'));

        $this->assertSame('Kurikulum Merdeka Belajar', Setting::get('curriculum_name'));
        $this->assertSame('Pembelajaran berbasis projek dengan penguatan profil pelajar Pancasila.', Setting::get('curriculum_description'));

        $this->get(route('curriculum'))
            ->assertOk()
            ->assertSee('Kurikulum Merdeka Belajar')
            ->assertSee('Pembelajaran berbasis projek dengan penguatan profil pelajar Pancasila.');
    }
}
