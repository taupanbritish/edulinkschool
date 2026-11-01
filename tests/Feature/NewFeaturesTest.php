<?php

namespace Tests\Feature;

use App\Imports\ClassroomImporter;
use App\Imports\StudentImporter;
use App\Imports\SubjectImporter;
use App\Imports\TeacherImporter;
use App\Models\Announcement;
use App\Models\Classroom;
use App\Models\Guardian;
use App\Models\OrganizationMember;
use App\Models\Schedule;
use App\Models\Setting;
use App\Models\Staff;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeachingAssignment;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class NewFeaturesTest extends TestCase
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

    private function xlsx(array $headers, array $rows): UploadedFile
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray($headers, null, 'A1');

        if ($rows !== []) {
            $sheet->fromArray($rows, null, 'A2');
        }

        $path = tempnam(sys_get_temp_dir(), 'impor').'.xlsx';
        (new Xlsx($spreadsheet))->save($path);

        return new UploadedFile($path, 'import.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);
    }

    public function test_public_staff_and_organization_pages_render_seeded_data(): void
    {
        $this->get(route('staff'))
            ->assertOk()
            ->assertSee('Wati Sumiati')
            ->assertSee('Kepala Tata Usaha');

        $this->get(route('organization'))
            ->assertOk()
            ->assertSee('Dra. Siti Rahmawati')
            ->assertSee('Wakil Kepala Bidang Kurikulum')
            ->assertSee('Koordinator Bimbingan Konseling');
    }

    public function test_admin_manages_staff_photo_and_media_route(): void
    {
        $this->actingAs($this->admin());

        $this->post(route('admin.staff.store'), [
            'nip' => '199901012020011099',
            'name' => 'Staf Foto',
            'gender' => 'P',
            'position' => 'Staf Administrasi',
            'phone' => '081200000000',
            'email' => 'staf.foto@sekolah.id',
            'sort_order' => 5,
            'is_active' => 1,
            'photo' => UploadedFile::fake()->image('staf.jpg', 400, 400),
        ])->assertRedirect(route('admin.staff.index'));

        $staff = Staff::where('name', 'Staf Foto')->firstOrFail();
        $this->assertNotNull($staff->photo_path);
        Storage::disk('public')->assertExists($staff->photo_path);

        $this->get('/media/'.$staff->photo_path)
            ->assertOk()
            ->assertHeader('Access-Control-Allow-Origin', '*');

        $this->get('/media/photos/../secret.txt')->assertNotFound();
        $this->get('/media/lain/file.jpg')->assertNotFound();

        $this->get(route('admin.staff.index'))->assertOk()->assertSee('Staf Foto');

        $oldPath = $staff->photo_path;

        $this->put(route('admin.staff.update', $staff), [
            'nip' => $staff->nip,
            'name' => 'Staf Foto',
            'gender' => 'P',
            'position' => 'Staf Administrasi',
            'is_active' => 1,
            'remove_photo' => 1,
        ])->assertRedirect(route('admin.staff.index'));

        $staff->refresh();
        $this->assertNull($staff->photo_path);
        Storage::disk('public')->assertMissing($oldPath);

        $this->delete(route('admin.staff.destroy', $staff))->assertRedirect(route('admin.staff.index'));
        $this->assertDatabaseMissing('staff', ['id' => $staff->id]);
    }

    public function test_admin_manages_organization_tree_without_cycles(): void
    {
        $this->actingAs($this->admin());

        $this->post(route('admin.organization.store'), [
            'name' => 'Ketua Komite',
            'position' => 'Komite Sekolah',
            'sort_order' => 9,
        ])->assertRedirect(route('admin.organization.index'));

        $root = OrganizationMember::where('name', 'Ketua Komite')->firstOrFail();

        $this->post(route('admin.organization.store'), [
            'parent_id' => $root->id,
            'name' => 'Anggota Komite',
            'position' => 'Anggota',
            'sort_order' => 1,
        ])->assertRedirect(route('admin.organization.index'));

        $child = OrganizationMember::where('name', 'Anggota Komite')->firstOrFail();
        $this->assertSame($root->id, $child->parent_id);

        $this->put(route('admin.organization.update', $child), [
            'parent_id' => $child->id,
            'name' => 'Anggota Komite',
            'position' => 'Anggota',
            'sort_order' => 1,
        ])->assertSessionHasErrors('parent_id');

        $this->get(route('admin.organization.index'))
            ->assertOk()
            ->assertSee('Ketua Komite')
            ->assertSee('Anggota Komite');
    }

    public function test_import_students_from_excel(): void
    {
        $this->actingAs($this->admin());

        $headers = array_keys(StudentImporter::COLUMNS);
        $file = $this->xlsx($headers, [
            ['26279901', '0000599901', 'Impor Siswa', 'L', 'Bandung', '2011-01-01', 'X-1', 'Jl. Impor', 'aktif', '2026', 'impor.siswa@sekolah.id', ''],
            ['', '', 'Tanpa NIS', 'P', '', '', 'X-1', '', '', '', '', ''],
        ]);

        $response = $this->post(route('admin.imports.store', 'siswa'), ['file' => $file])
            ->assertRedirect(route('admin.imports.form', 'siswa'));

        $result = session('import_result');
        $this->assertSame(1, $result['imported']);
        $this->assertCount(1, $result['errors']);
        $this->assertStringContainsString('NIS wajib diisi', $result['errors'][0]);

        $student = Student::where('nis', '26279901')->firstOrFail();
        $this->assertSame('Impor Siswa', $student->name);
        $this->assertSame('siswa', $student->user->role);
    }

    public function test_import_teachers_classrooms_and_subjects_from_excel(): void
    {
        $this->actingAs($this->admin());

        $this->post(route('admin.imports.store', 'guru'), ['file' => $this->xlsx(
            array_keys(TeacherImporter::COLUMNS),
            [['198001012020011001', 'Guru Impor, S.Pd.', 'P', '08123456789', 'impor.guru@sekolah.id', '']]
        )])->assertRedirect(route('admin.imports.form', 'guru'));

        $this->assertSame(1, session('import_result')['imported']);
        $teacher = Teacher::where('nip', '198001012020011001')->firstOrFail();
        $this->assertSame('guru', $teacher->user->role);

        $this->post(route('admin.imports.store', 'kelas'), ['file' => $this->xlsx(
            array_keys(ClassroomImporter::COLUMNS),
            [['XII IPA 1', '12', '2026/2027', $teacher->nip]]
        )])->assertRedirect(route('admin.imports.form', 'kelas'));

        $this->assertSame(1, session('import_result')['imported']);
        $classroom = Classroom::where('name', 'XII IPA 1')->firstOrFail();
        $this->assertSame(12, $classroom->grade_level);
        $this->assertSame($teacher->id, $classroom->homeroom_teacher_id);

        $this->post(route('admin.imports.store', 'mapel'), ['file' => $this->xlsx(
            array_keys(SubjectImporter::COLUMNS),
            [['IMP', 'Mapel Impor', 'Deskripsi mapel impor.']]
        )])->assertRedirect(route('admin.imports.form', 'mapel'));

        $this->assertSame(1, session('import_result')['imported']);
        $this->assertDatabaseHas('subjects', ['code' => 'IMP', 'name' => 'Mapel Impor']);
    }

    public function test_import_template_downloads(): void
    {
        $this->actingAs($this->admin());

        $this->get(route('admin.imports.template', 'siswa'))
            ->assertOk()
            ->assertDownload('template-impor-siswa.xlsx');

        $this->get(route('admin.imports.form', 'mapel'))
            ->assertOk()
            ->assertSee('kode')
            ->assertSee('Unduh Template');
    }

    public function test_api_exposes_student_and_teacher_photos(): void
    {
        $login = $this->postJson('/api/login', [
            'email' => 'siswa@sekolah.id',
            'password' => 'siswa123',
        ])->assertOk();

        $token = $login->json('token');

        $beranda = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/beranda')
            ->assertOk()
            ->json();

        $this->assertStringContainsString('/media/photos/demo/siswa-ananda.jpg', $beranda['siswa']['foto']);

        $jadwal = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/jadwal')
            ->assertOk()
            ->json();

        $lessons = collect($jadwal['hari'])->flatMap(fn ($day) => $day['jadwal']);
        $withPhoto = $lessons->first(fn ($lesson) => filled($lesson['guru_foto'] ?? null));

        $this->assertNotNull($withPhoto);
        $this->assertStringContainsString('/media/photos/demo/', $withPhoto['guru_foto']);

        $nilai = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/nilai')
            ->assertOk()
            ->json();

        $subjectPhotos = collect($nilai['mapel'])->pluck('guru_foto')->filter();
        $this->assertNotEmpty($subjectPhotos);
    }

    public function test_admin_dashboard_shows_staff_summary_without_recap_panel(): void
    {
        $this->actingAs($this->admin())
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Tata Usaha')
            ->assertSee('3 staf tercatat')
            ->assertDontSee('Rekap Tata Usaha')
            ->assertDontSee('Total staf')
            ->assertSee('Panel Administrasi');
    }

    public function test_admin_uploads_school_logo_and_favicon(): void
    {
        $this->actingAs($this->admin());

        $this->put(route('admin.settings.update'), [
            'school_name' => 'SMA Edulink School',
            'school_short' => 'Edulink School',
            'logo' => UploadedFile::fake()->image('logo.png', 240, 240),
            'favicon' => UploadedFile::fake()->image('favicon.png', 64, 64),
        ])->assertRedirect(route('admin.settings.edit'));

        $logo = Setting::get('logo_path');
        $favicon = Setting::get('favicon_path');

        $this->assertNotNull($logo);
        $this->assertNotNull($favicon);
        $this->assertStringStartsWith('photos/branding/', $logo);
        $this->assertStringStartsWith('photos/branding/', $favicon);
        Storage::disk('public')->assertExists($logo);
        Storage::disk('public')->assertExists($favicon);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('media/'.$logo, false)
            ->assertSee('media/'.$favicon, false)
            ->assertSee('rel="icon"', false);

        $this->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('media/'.$logo, false);

        $this->getJson('/api/sekolah')
            ->assertOk()
            ->assertJsonPath('logo', url('/media/'.$logo))
            ->assertJsonPath('inisial', 'ED');

        auth()->logout();

        $this->get(route('login'))
            ->assertOk()
            ->assertSee('media/'.$logo, false);
    }

    public function test_admin_uploads_principal_photo_and_home_shows_it(): void
    {
        $this->actingAs($this->admin());

        $this->put(route('admin.settings.update'), [
            'school_name' => 'SMA Edulink School',
            'school_short' => 'Edulink School',
            'principal_name' => 'Dra. Siti Rahmawati, M.Pd.',
            'principal_photo' => UploadedFile::fake()->image('kepsek.jpg', 480, 480),
        ])->assertRedirect(route('admin.settings.edit'));

        $path = Setting::get('principal_photo_path');

        $this->assertNotNull($path);
        $this->assertStringStartsWith('photos/principal/', $path);
        Storage::disk('public')->assertExists($path);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('media/'.$path, false)
            ->assertSee('Foto Dra. Siti Rahmawati, M.Pd.', false);

        $this->get(route('admin.settings.edit'))
            ->assertOk()
            ->assertSee('media/'.$path, false)
            ->assertSee('principal_photo');

        $this->put(route('admin.settings.update'), [
            'school_name' => 'SMA Edulink School',
            'school_short' => 'Edulink School',
            'remove_principal_photo' => '1',
        ])->assertRedirect(route('admin.settings.edit'));

        $this->assertNull(Setting::get('principal_photo_path'));
        Storage::disk('public')->assertMissing($path);

        $this->get(route('home'))->assertOk()->assertDontSee('principal-photo', false);
    }

    public function test_admin_customizes_home_hero_text(): void
    {
        $this->actingAs($this->admin());

        $this->put(route('admin.settings.update'), [
            'school_name' => 'SMA Edulink School',
            'school_short' => 'Edulink School',
            'hero_title' => 'Belajar Bermakna *Bersama* Kami',
            'hero_subtitle' => 'Subjudul khusus dari admin untuk halaman beranda.',
        ])->assertRedirect(route('admin.settings.edit'));

        $this->assertSame('Belajar Bermakna *Bersama* Kami', Setting::get('hero_title'));
        $this->assertSame('Subjudul khusus dari admin untuk halaman beranda.', Setting::get('hero_subtitle'));

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Belajar Bermakna', false)
            ->assertSee('<span class="highlight">Bersama</span>', false)
            ->assertDontSee('Membentuk Generasi', false)
            ->assertSee('Subjudul khusus dari admin untuk halaman beranda.');

        $this->get(route('admin.settings.edit'))
            ->assertOk()
            ->assertSee('hero_title')
            ->assertSee('hero_subtitle');
    }

    public function test_home_shows_youtube_video_from_settings(): void
    {
        Setting::set('youtube_url', 'https://youtu.be/abcdefghijk');

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Galeri Video')
            ->assertSee('https://www.youtube-nocookie.com/embed/abcdefghijk', false);
    }

    public function test_site_menu_order_and_profile_submenu(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSeeInOrder([
                'Beranda',
                'Profil Sekolah',
                'Struktur Organisasi',
                'Guru',
                'Tata Usaha',
                'Berita & Pengumuman',
                'Kontak',
            ]);
    }

    public function test_admin_generates_missing_accounts_for_teachers_students_and_guardians(): void
    {
        $teacher = Teacher::create([
            'nip' => '999900000000000001',
            'name' => 'Guru Tanpa Akun',
            'gender' => 'L',
        ]);
        $student = Student::create([
            'nis' => '99990001',
            'name' => 'Siswa Tanpa Akun',
            'gender' => 'L',
            'classroom_id' => Classroom::firstOrFail()->id,
            'status' => 'aktif',
            'entry_year' => 2026,
        ]);
        $guardian = Guardian::create([
            'name' => 'Wali Tanpa Akun',
            'phone' => '0812-9999-8888',
        ]);

        $this->actingAs($this->admin());

        $this->get(route('admin.teachers.index'))
            ->assertOk()
            ->assertSee('Generate 1 Akun');

        $this->post(route('admin.accounts.generate', 'guru'))
            ->assertRedirect(route('admin.teachers.index'))
            ->assertSessionHas('success');

        $this->get(route('admin.teachers.index'))
            ->assertOk()
            ->assertSee('Semua guru sudah berakun');
        $this->post(route('admin.accounts.generate', 'siswa'))
            ->assertRedirect(route('admin.students.index'))
            ->assertSessionHas('success');
        $this->post(route('admin.accounts.generate', 'wali'))
            ->assertRedirect(route('admin.guardians.index'))
            ->assertSessionHas('success');

        foreach ([[$teacher, 'guru.guru-tanpa-akun.0001@sekolah.id', 'guru123'], [$student, 'siswa.siswa-tanpa-akun.0001@sekolah.id', 'siswa123'], [$guardian, 'wali.wali-tanpa-akun.8888@sekolah.id', 'ortu123']] as [$record, $email, $password]) {
            $record->refresh();
            $this->assertNotNull($record->user_id);
            $this->assertSame($email, $record->user->email);
            $this->assertTrue(Hash::check($password, $record->user->password));
        }

        $this->assertSame('guru', $teacher->user->role);
        $this->assertSame('siswa', $student->user->role);
        $this->assertSame('orang_tua', $guardian->user->role);
    }

    public function test_student_edit_form_offers_searchable_guardian_picker(): void
    {
        $this->actingAs($this->admin());

        $student = Student::whereNotNull('guardian_id')->with('guardian')->firstOrFail();

        $this->get(route('admin.students.edit', $student))
            ->assertOk()
            ->assertSee('Cari nama wali murid')
            ->assertSee('data-combo', false)
            ->assertSee('data-combo-input', false)
            ->assertSee('name="guardian_id"', false)
            ->assertSee('id="guardian_id"', false)
            ->assertSee($student->guardian->name);
    }

    public function test_admin_student_form_saves_selected_guardian(): void
    {
        $this->actingAs($this->admin());

        $student = Student::with('guardian')->whereNotNull('guardian_id')->firstOrFail();
        $guardian = Guardian::where('id', '!=', $student->guardian_id)->firstOrFail();

        $payload = [
            'nis' => $student->nis,
            'name' => $student->name,
            'gender' => $student->gender,
            'classroom_id' => $student->classroom_id,
            'status' => $student->status,
            'entry_year' => $student->entry_year,
        ];

        $this->put(route('admin.students.update', $student), [...$payload, 'guardian_id' => $guardian->id])
            ->assertRedirect(route('admin.students.index'));

        $this->assertSame($guardian->id, $student->fresh()->guardian_id);

        $this->put(route('admin.students.update', $student), [...$payload, 'guardian_id' => ''])
            ->assertRedirect(route('admin.students.index'));

        $this->assertNull($student->fresh()->guardian_id);
    }

    public function test_admin_can_delete_guardian_and_linked_students_are_detached(): void
    {
        $this->actingAs($this->admin());

        $student = Student::whereNotNull('guardian_id')->firstOrFail();
        $guardian = $student->guardian;
        $linked = $guardian->students()->count();

        $this->delete(route('admin.guardians.destroy', $guardian))
            ->assertRedirect(route('admin.guardians.index'))
            ->assertSessionHas('success', fn (string $message) => str_contains($message, $guardian->name)
                && str_contains($message, "{$linked} siswa"));

        $this->assertModelMissing($guardian);
        $this->assertNull($student->fresh()->guardian_id);
    }

    public function test_admin_subject_form_creates_teaching_assignment_and_schedule(): void
    {
        $this->actingAs($this->admin());

        $classroom = Classroom::firstOrFail();
        $teacher = Teacher::firstOrFail();

        $this->get(route('admin.subjects.create'))
            ->assertOk()
            ->assertSee('Penugasan Jadwal');

        $this->post(route('admin.subjects.store'), [
            'code' => 'SEN',
            'name' => 'Seni Budaya',
            'description' => 'Seni musik dan rupa.',
            'classroom_id' => $classroom->id,
            'teacher_id' => $teacher->id,
            'day' => 3,
            'starts_at' => '09:00',
            'ends_at' => '10:30',
            'room' => 'Aula',
        ])->assertRedirect(route('admin.subjects.index'));

        $subject = Subject::where('code', 'SEN')->firstOrFail();
        $assignment = TeachingAssignment::where([
            'classroom_id' => $classroom->id,
            'subject_id' => $subject->id,
            'academic_year' => $classroom->academic_year,
        ])->firstOrFail();

        $this->assertSame($teacher->id, $assignment->teacher_id);
        $this->assertDatabaseHas('schedules', [
            'teaching_assignment_id' => $assignment->id,
            'day' => 3,
            'starts_at' => '09:00:00',
            'ends_at' => '10:30:00',
            'room' => 'Aula',
        ]);

        $this->post(route('admin.subjects.store'), [
            'code' => 'SEN2',
            'name' => 'Seni Musik',
            'classroom_id' => $classroom->id,
        ])->assertSessionHasErrors(['teacher_id', 'day', 'starts_at', 'ends_at']);
    }

    public function test_admin_deletes_subject_with_its_assignments_and_schedules(): void
    {
        $this->actingAs($this->admin());

        $subject = Subject::create(['code' => 'HPS', 'name' => 'Mapel Akan Dihapus']);
        $assignment = TeachingAssignment::create([
            'classroom_id' => Classroom::firstOrFail()->id,
            'subject_id' => $subject->id,
            'teacher_id' => Teacher::firstOrFail()->id,
            'academic_year' => Classroom::firstOrFail()->academic_year,
        ]);
        $schedule = Schedule::create([
            'teaching_assignment_id' => $assignment->id,
            'day' => 2,
            'starts_at' => '07:00:00',
            'ends_at' => '08:30:00',
        ]);

        $this->delete(route('admin.subjects.destroy', $subject))
            ->assertRedirect(route('admin.subjects.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('subjects', ['id' => $subject->id]);
        $this->assertDatabaseMissing('teaching_assignments', ['id' => $assignment->id]);
        $this->assertDatabaseMissing('schedules', ['id' => $schedule->id]);
    }

    public function test_subject_form_edits_and_deletes_assignments_and_schedules(): void
    {
        $this->actingAs($this->admin());

        $subject = Subject::create(['code' => 'HPS2', 'name' => 'Mapel Dengan Jadwal']);
        $assignment = TeachingAssignment::create([
            'classroom_id' => Classroom::firstOrFail()->id,
            'subject_id' => $subject->id,
            'teacher_id' => Teacher::firstOrFail()->id,
            'academic_year' => Classroom::firstOrFail()->academic_year,
        ]);
        $schedule = Schedule::create([
            'teaching_assignment_id' => $assignment->id,
            'day' => 4,
            'starts_at' => '10:00:00',
            'ends_at' => '11:30:00',
        ]);

        $this->get(route('admin.subjects.edit', $subject))
            ->assertOk()
            ->assertSee('Hapus penugasan')
            ->assertSee('Hapus jadwal')
            ->assertSee('delete-assignment-'.$assignment->id, false)
            ->assertSee('delete-schedule-'.$schedule->id, false)
            ->assertSee(route('admin.schedules.edit', $schedule), false)
            ->assertSee(route('admin.schedules.create', ['assignment' => $assignment->id]), false);

        $this->from(route('admin.subjects.edit', $subject))
            ->delete(route('admin.schedules.destroy', $schedule))
            ->assertRedirect(route('admin.subjects.edit', $subject))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('schedules', ['id' => $schedule->id]);

        $this->delete(route('admin.subjects.assignments.destroy', [$subject, $assignment]))
            ->assertRedirect(route('admin.subjects.edit', $subject))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('teaching_assignments', ['id' => $assignment->id]);
        $this->assertDatabaseMissing('schedules', ['id' => $schedule->id]);
        $this->assertDatabaseHas('subjects', ['id' => $subject->id]);
    }

    public function test_seeded_announcements_show_photos_and_youtube_videos_publicly(): void
    {
        $this->assertSame(8, Announcement::count());
        $this->assertSame(8, Announcement::has('photos')->count());
        $this->assertSame(5, Announcement::has('videos')->count());
        Storage::disk('public')->assertExists('photos/demo/berita-osn.jpg');

        $osn = Announcement::where('title', 'Siswa Edulink School Raih Medali Emas OSN Informatika')->firstOrFail();

        $this->get(route('news.index'))
            ->assertOk()
            ->assertSee('Siswa Edulink School Raih Medali Emas OSN Informatika')
            ->assertSee('/media/photos/demo/berita-osn.jpg', false);

        $this->get(route('news.show', $osn))
            ->assertOk()
            ->assertSee('Galeri')
            ->assertSee('1 foto')
            ->assertSee('/media/photos/demo/berita-osn.jpg', false)
            ->assertSee('youtube-nocookie.com/embed/M7lc1UVf-VE', false)
            ->assertSee('Cuplikan prosesi penyerahan medali OSN 2026.');

        $this->get('/media/photos/demo/berita-osn.jpg')
            ->assertOk()
            ->assertHeader('Access-Control-Allow-Origin', '*');
    }

    private function announcementPayload(array $overrides = []): array
    {
        return $overrides + [
            'title' => 'Pekan Olahraga Sekolah',
            'category' => 'berita',
            'excerpt' => 'Dokumentasi pekan olahraga.',
            'body' => "Kegiatan berlangsung meriah.\n\nSeluruh kelas ikut serta.",
            'is_published' => '1',
        ];
    }

    public function test_admin_uploads_announcement_gallery_and_public_page_shows_it(): void
    {
        $this->actingAs($this->admin());

        $this->post(route('admin.announcements.store'), $this->announcementPayload([
            'photos' => [
                UploadedFile::fake()->image('pembukaan.jpg', 1200, 800),
                UploadedFile::fake()->image('final.png', 1000, 750),
            ],
            'videos' => [
                UploadedFile::fake()->create('highlight.mp4', 2048, 'video/mp4'),
            ],
            'video_urls' => "https://youtu.be/abcdefghijk\nhttps://www.youtube.com/watch?v=lmnopqrstuv",
        ]))->assertRedirect(route('admin.announcements.index'));

        $announcement = Announcement::where('title', 'Pekan Olahraga Sekolah')->firstOrFail();

        $this->assertSame(5, $announcement->attachments()->count());
        $this->assertSame(2, $announcement->photos()->count());
        $this->assertSame(3, $announcement->videos()->count());

        foreach ($announcement->attachments()->whereNotNull('path')->pluck('path') as $path) {
            Storage::disk('public')->assertExists($path);
        }

        $this->get(route('news.show', $announcement))
            ->assertOk()
            ->assertSee('Galeri')
            ->assertSee('2 foto')
            ->assertSee('youtube-nocookie.com/embed/abcdefghijk', false)
            ->assertSee('youtube-nocookie.com/embed/lmnopqrstuv', false);

        $this->get(route('news.index'))
            ->assertOk()
            ->assertSee('Pekan Olahraga Sekolah');

        $this->get('/media/'.$announcement->photos()->value('path'))
            ->assertOk()
            ->assertHeader('Access-Control-Allow-Origin', '*');

        $this->getJson('/api/pengumuman/'.$announcement->slug)
            ->assertOk()
            ->assertJsonCount(5, 'data.galeri')
            ->assertJsonPath('data.galeri.0.tipe', 'foto')
            ->assertJsonPath('data.galeri.4.tipe', 'video');
    }

    public function test_admin_updates_captions_and_removes_announcement_attachment(): void
    {
        $this->actingAs($this->admin());

        $this->post(route('admin.announcements.store'), $this->announcementPayload([
            'photos' => [UploadedFile::fake()->image('upacara.jpg', 800, 600)],
            'video_urls' => 'https://youtu.be/abcdefghijk',
        ]));

        $announcement = Announcement::where('title', 'Pekan Olahraga Sekolah')->firstOrFail();
        $photo = $announcement->photos()->firstOrFail();
        $video = $announcement->videos()->firstOrFail();
        $path = $photo->path;

        $this->put(route('admin.announcements.update', $announcement), $this->announcementPayload([
            'captions' => [
                $photo->id => 'Foto pembuka',
                $video->id => 'Cuplikan acara',
            ],
            'remove_attachments' => [$photo->id],
        ]))->assertRedirect(route('admin.announcements.index'));

        $this->assertDatabaseMissing('announcement_attachments', ['id' => $photo->id]);
        Storage::disk('public')->assertMissing($path);
        $this->assertDatabaseHas('announcement_attachments', [
            'id' => $video->id,
            'caption' => 'Cuplikan acara',
        ]);

        $this->delete(route('admin.announcements.destroy', $announcement))
            ->assertRedirect(route('admin.announcements.index'));

        $this->assertDatabaseMissing('announcement_attachments', ['announcement_id' => $announcement->id]);
    }

    public function test_announcement_gallery_rejects_non_youtube_video_links(): void
    {
        $this->actingAs($this->admin());

        $this->post(route('admin.announcements.store'), $this->announcementPayload([
            'video_urls' => 'https://vimeo.com/123456789',
        ]))->assertSessionHasErrors('video_urls');

        $this->assertDatabaseMissing('announcements', ['title' => 'Pekan Olahraga Sekolah']);
    }
}
