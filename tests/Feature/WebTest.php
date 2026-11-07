<?php

namespace Tests\Feature;

use App\Models\Announcement;
use App\Models\Classroom;
use App\Models\Setting;
use App\Models\Staff;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
    }

    public function test_public_pages_render(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('SMA Edulink School')
            ->assertSee('Sistem Informasi Sekolah · Edulink School')
            ->assertDontSee('Laravel + PostgreSQL');

        $this->get('/profil')->assertOk();
        $this->get('/kontak')->assertOk();
        $this->get('/berita')->assertOk();
    }

    public function test_home_shows_school_statistics(): void
    {
        $response = $this->get('/')
            ->assertOk()
            ->assertSee('Sekolah dalam Angka')
            ->assertSee('<span>Siswa aktif</span>', false)
            ->assertSee('<span>Guru</span>', false)
            ->assertSee('<span>Tata Usaha</span>', false)
            ->assertSee('<span>Rombongan belajar</span>', false)
            ->assertDontSee('Prestasi 3 tahun terakhir');

        $totals = [
            Student::where('status', 'aktif')->count(),
            Teacher::count(),
            Staff::where('is_active', true)->count(),
            Classroom::count(),
        ];

        foreach ($totals as $total) {
            $response->assertSee('<strong>'.number_format($total, 0, ',', '.').'</strong>', false);
        }
    }

    public function test_profile_lists_teachers_and_staff_with_pagination_above_four(): void
    {
        foreach ([1, 2] as $index) {
            Teacher::create([
                'nip' => '99990000000000010'.$index,
                'name' => 'Zulkifli Guru '.$index,
                'gender' => 'L',
            ]);

            Staff::create([
                'name' => 'Zulaikha Staf '.$index,
                'gender' => 'P',
                'position' => 'Staf Administrasi',
                'sort_order' => 10 + $index,
                'is_active' => true,
            ]);
        }

        $response = $this->get('/profil')
            ->assertOk()
            ->assertSeeInOrder(['Guru & wali kelas', 'Tata Usaha'])
            ->assertSee('Lihat semua guru')
            ->assertSee('Lihat semua tata usaha')
            ->assertDontSee('Zulkifli Guru 1')
            ->assertDontSee('Zulaikha Staf 2');

        $this->assertSame(2, substr_count($response->getContent(), 'aria-label="Navigasi halaman"'));
        $this->assertSame(2, substr_count($response->getContent(), 'aria-label="Halaman 2"'));

        $this->get('/profil?guru_page=2')
            ->assertOk()
            ->assertSee('Zulkifli Guru 1');

        $this->get('/profil?staf_page=2')
            ->assertOk()
            ->assertSee('Zulaikha Staf 2');
    }

    public function test_teacher_page_lists_all_teachers_separately(): void
    {
        $teachers = Teacher::orderBy('name')->get();

        $this->get(route('teachers'))
            ->assertOk()
            ->assertSee('Guru & wali kelas');

        foreach ($teachers as $teacher) {
            $this->get(route('teachers'))->assertSee($teacher->name);
        }

        $this->get(route('home'))
            ->assertSee(route('teachers'), false);
    }

    public function test_teacher_cards_show_subjects_and_homeroom_class(): void
    {
        $teacher = Teacher::whereHas('classrooms')
            ->with(['assignments.subject', 'classrooms'])
            ->firstOrFail();

        $subjects = $teacher->assignments->pluck('subject.name')->unique()->sort()->values();

        $this->assertNotEmpty($subjects);

        $this->get(route('teachers'))
            ->assertOk()
            ->assertSee('Mengajar: '.$subjects->join(', '))
            ->assertSee('Wali kelas '.$teacher->classrooms->first()->name)
            ->assertSee('NIP '.$teacher->nip);
    }

    public function test_published_announcement_can_be_read_and_unpublished_is_hidden(): void
    {
        $published = Announcement::published()->firstOrFail();

        $this->get('/berita/'.$published->slug)
            ->assertOk()
            ->assertSee($published->title);

        $draft = Announcement::create([
            'title' => 'Draf Internal',
            'slug' => 'draf-internal',
            'body' => 'Belum boleh dibaca publik.',
            'category' => 'berita',
            'is_published' => false,
        ]);

        $this->get('/berita/'.$draft->slug)->assertNotFound();
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get('/admin')->assertRedirect('/login');
        $this->get('/guru')->assertRedirect('/login');
    }

    public function test_admin_can_login_and_open_dashboard(): void
    {
        $this->post('/login', ['email' => 'admin@sekolah.id', 'password' => 'admin123'])
            ->assertRedirect('/dashboard');

        $this->get('/admin')->assertOk();
        $this->assertAuthenticatedAs(User::where('email', 'admin@sekolah.id')->first());
    }

    public function test_teacher_is_forbidden_from_admin_area_but_can_open_teacher_area(): void
    {
        $this->post('/login', ['email' => 'guru@sekolah.id', 'password' => 'guru123'])
            ->assertRedirect('/dashboard');

        $this->get('/admin')->assertForbidden();
        $this->get('/guru')->assertOk();
    }

    public function test_student_cannot_open_admin_or_teacher_area(): void
    {
        $this->post('/login', ['email' => 'siswa@sekolah.id', 'password' => 'siswa123'])
            ->assertRedirect('/dashboard');

        $this->get('/admin')->assertForbidden();
        $this->get('/guru')->assertForbidden();
    }

    public function test_wrong_password_returns_validation_error(): void
    {
        $this->from('/login')
            ->post('/login', ['email' => 'admin@sekolah.id', 'password' => 'salah'])
            ->assertRedirect('/login')
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_logout_returns_to_homepage(): void
    {
        $this->post('/login', ['email' => 'admin@sekolah.id', 'password' => 'admin123']);

        $this->post('/logout')->assertRedirect('/');

        $this->assertGuest();
    }

    public function test_admin_can_update_school_settings(): void
    {
        $this->post('/login', ['email' => 'admin@sekolah.id', 'password' => 'admin123']);

        $this->put('/admin/settings', [
            'school_name' => 'SMA Edulink School',
            'school_short' => 'Edulink School',
            'phone' => '(022) 999-0000',
        ])->assertRedirect(route('admin.settings.edit'));

        $this->assertDatabaseHas('settings', ['key' => 'school_short', 'value' => 'Edulink School']);
        $this->assertSame('(022) 999-0000', Setting::get('phone'));
    }
}
