<?php

namespace Tests\Feature;

use App\Models\Announcement;
use App\Models\Student;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
    }

    private function tokenFor(string $email, string $password): string
    {
        $response = $this->postJson('/api/login', [
            'email' => $email,
            'password' => $password,
        ]);

        $response->assertOk();

        return $response->json('token');
    }

    public function test_public_school_profile_is_available(): void
    {
        $this->getJson('/api/sekolah')
            ->assertOk()
            ->assertJsonStructure(['nama', 'nama_singkat', 'slogan', 'alamat', 'telepon', 'kepala_sekolah', 'logo', 'inisial'])
            ->assertJsonPath('nama', 'SMA Edulink School')
            ->assertJsonPath('inisial', 'ED');
    }

    public function test_public_announcements_are_exposed(): void
    {
        $this->getJson('/api/pengumuman')
            ->assertOk()
            ->assertJsonStructure([
                'data' => [['judul', 'slug', 'kategori', 'ringkasan', 'isi', 'diterbitkan_label']],
            ]);

        $slug = Announcement::published()->value('slug');

        $this->getJson('/api/pengumuman/'.$slug)
            ->assertOk()
            ->assertJsonPath('data.slug', $slug);
    }

    public function test_student_can_login_and_receives_token_and_student_data(): void
    {
        $this->postJson('/api/login', [
            'email' => 'siswa@sekolah.id',
            'password' => 'siswa123',
            'device_name' => 'phpunit',
        ])
            ->assertOk()
            ->assertJsonStructure([
                'token',
                'token_type',
                'user' => ['id', 'nama', 'peran', 'peran_label'],
                'siswa' => [['id', 'nama', 'kelas']],
            ])
            ->assertJsonPath('user.peran', 'siswa');
    }

    public function test_non_student_accounts_cannot_login_to_the_mobile_api(): void
    {
        $this->postJson('/api/login', [
            'email' => 'admin@sekolah.id',
            'password' => 'admin123',
        ])->assertStatus(422)->assertJsonValidationErrors('email');
    }

    public function test_inactive_accounts_are_rejected(): void
    {
        User::create([
            'name' => 'Siswa Nonaktif',
            'email' => 'nonaktif@sekolah.id',
            'password' => 'rahasia123',
            'role' => User::ROLE_SISWA,
            'is_active' => false,
        ]);

        $this->postJson('/api/login', [
            'email' => 'nonaktif@sekolah.id',
            'password' => 'rahasia123',
        ])->assertStatus(422)->assertJsonValidationErrors('email');
    }

    public function test_wrong_password_is_rejected(): void
    {
        $this->postJson('/api/login', [
            'email' => 'siswa@sekolah.id',
            'password' => 'salah-password',
        ])->assertStatus(422)->assertJsonValidationErrors('email');
    }

    public function test_guests_cannot_access_student_endpoints(): void
    {
        $this->getJson('/api/beranda')->assertStatus(401);
    }

    public function test_student_can_access_all_dashboard_endpoints(): void
    {
        $token = $this->tokenFor('siswa@sekolah.id', 'siswa123');
        $headers = ['Authorization' => "Bearer {$token}"];

        $this->withHeaders($headers)->getJson('/api/beranda')
            ->assertOk()
            ->assertJsonStructure([
                'user' => ['nama'],
                'siswa' => ['id', 'nama', 'kelas'],
                'anak',
                'ringkasan' => ['rata_nilai', 'jumlah_mapel', 'kehadiran_persen', 'hadir', 'sakit', 'izin', 'alpa'],
                'jadwal_hari_ini',
                'nilai_terbaru',
                'absensi_terbaru',
                'pengumuman_terbaru',
            ]);

        $this->withHeaders($headers)->getJson('/api/jadwal')
            ->assertOk()
            ->assertJsonCount(6, 'hari')
            ->assertJsonStructure(['hari' => [['hari', 'nama', 'jadwal']]]);

        $this->withHeaders($headers)->getJson('/api/nilai')
            ->assertOk()
            ->assertJsonStructure([
                'rata_rata',
                'total',
                'mapel' => [['mapel' => ['nama'], 'guru', 'rata_rata', 'nilai']],
            ]);

        $this->withHeaders($headers)->getJson('/api/absensi')
            ->assertOk()
            ->assertJsonStructure([
                'periode' => ['bulan', 'tahun', 'label'],
                'ringkasan' => ['hadir', 'sakit', 'izin', 'alpa', 'persen_hadir'],
                'catatan',
            ]);

        $this->withHeaders($headers)->getJson('/api/profil')
            ->assertOk()
            ->assertJsonStructure(['user' => ['nama'], 'siswa' => ['nama', 'wali'], 'anak']);
    }

    public function test_student_cannot_read_another_students_data(): void
    {
        $token = $this->tokenFor('siswa@sekolah.id', 'siswa123');
        $other = Student::whereNull('user_id')->firstOrFail();

        $this->withHeaders(['Authorization' => "Bearer {$token}"])
            ->getJson('/api/beranda?student_id='.$other->id)
            ->assertStatus(403);
    }

    public function test_parent_can_switch_between_children(): void
    {
        $response = $this->postJson('/api/login', [
            'email' => 'ortu@sekolah.id',
            'password' => 'ortu123',
        ]);

        $response->assertOk()->assertJsonCount(2, 'siswa');

        $token = $response->json('token');
        $headers = ['Authorization' => "Bearer {$token}"];
        $childIds = collect($response->json('siswa'))->pluck('id');

        $this->withHeaders($headers)
            ->getJson('/api/beranda?student_id='.$childIds[1])
            ->assertOk()
            ->assertJsonPath('siswa.id', $childIds[1]);

        $unrelated = Student::whereNotIn('id', $childIds)->firstOrFail();

        $this->withHeaders($headers)
            ->getJson('/api/beranda?student_id='.$unrelated->id)
            ->assertStatus(403);
    }

    public function test_logout_revokes_the_token(): void
    {
        $token = $this->tokenFor('siswa@sekolah.id', 'siswa123');
        $headers = ['Authorization' => "Bearer {$token}"];

        $this->withHeaders($headers)->postJson('/api/logout')->assertOk();

        $this->assertDatabaseCount('personal_access_tokens', 0);

        $this->app['auth']->forgetGuards();

        $this->withHeaders($headers)->getJson('/api/me')->assertStatus(401);
    }
}
