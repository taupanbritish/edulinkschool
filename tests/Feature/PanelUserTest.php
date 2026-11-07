<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PanelUserTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
    }

    private function admin(): User
    {
        return User::where('email', 'admin@sekolah.id')->firstOrFail();
    }

    private function panelUser(string $email): User
    {
        return User::where('email', $email)->firstOrFail();
    }

    public function test_seeded_panel_accounts_separate_principal_from_administrator(): void
    {
        $admin = $this->admin();
        $petugas = $this->panelUser('petugas@sekolah.id');
        $kepsek = $this->panelUser('kepsek@sekolah.id');

        $this->assertSame('Administrator Sekolah', $admin->name);
        $this->assertSame(User::ROLE_PETUGAS, $petugas->role);
        $this->assertSame(User::ROLE_KEPALA_SEKOLAH, $kepsek->role);
        $this->assertSame('Dra. Siti Rahmawati, M.Pd.', $kepsek->name);
        $this->assertTrue($petugas->isPanelUser());
        $this->assertTrue($kepsek->isPanelUser());
        $this->assertFalse($petugas->isAdmin());
        $this->assertFalse($kepsek->canManage());
    }

    public function test_admin_manages_panel_users(): void
    {
        $this->actingAs($this->admin());

        $this->get(route('admin.users.index'))
            ->assertOk()
            ->assertSee('Petugas Web Sekolah')
            ->assertSee('Kepala Sekolah')
            ->assertSee('Wakasek Kurikulum');

        $this->get(route('admin.users.create'))
            ->assertOk()
            ->assertSee('Petugas Web')
            ->assertSee('Kepala Sekolah')
            ->assertSee('Wakasek Kurikulum');

        $this->post(route('admin.users.store'), [
            'name' => 'Petugas Baru',
            'email' => 'petugas.baru@sekolah.id',
            'phone' => '081200000001',
            'role' => User::ROLE_PETUGAS,
            'password' => 'rahasia123',
            'is_active' => '1',
        ])->assertRedirect(route('admin.users.index'));

        $user = User::where('email', 'petugas.baru@sekolah.id')->firstOrFail();
        $this->assertSame(User::ROLE_PETUGAS, $user->role);
        $this->assertTrue($user->is_active);

        $this->get(route('admin.users.edit', $user))
            ->assertOk()
            ->assertSee('Petugas Baru');

        $this->put(route('admin.users.update', $user), [
            'name' => 'Petugas Diubah',
            'email' => 'petugas.baru@sekolah.id',
            'role' => User::ROLE_PETUGAS,
            'is_active' => '1',
        ])->assertRedirect(route('admin.users.index'));

        $this->assertSame('Petugas Diubah', $user->fresh()->name);

        $this->delete(route('admin.users.destroy', $user))
            ->assertRedirect(route('admin.users.index'));

        $this->assertModelMissing($user);
    }

    public function test_admin_cannot_delete_own_account_or_last_administrator(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin);

        $this->from(route('admin.users.index'))
            ->delete(route('admin.users.destroy', $admin))
            ->assertRedirect(route('admin.users.index'))
            ->assertSessionHas('error');

        $this->assertModelExists($admin);

        $other = User::create([
            'name' => 'Administrator Kedua',
            'email' => 'admin2@sekolah.id',
            'password' => 'admin123',
            'role' => User::ROLE_ADMIN,
            'is_active' => true,
        ]);

        $this->delete(route('admin.users.destroy', $other))
            ->assertRedirect(route('admin.users.index'));

        $this->assertModelMissing($other);
    }

    public function test_petugas_web_can_manage_data_but_not_settings_or_panel_users(): void
    {
        $this->actingAs($this->panelUser('petugas@sekolah.id'));

        $this->get(route('admin.dashboard'))->assertOk();
        $this->get(route('admin.students.index'))->assertOk();
        $this->get(route('admin.teachers.create'))->assertOk();

        $this->get(route('admin.settings.edit'))->assertForbidden();
        $this->get(route('admin.users.index'))->assertForbidden();
    }

    public function test_kepala_sekolah_can_view_data_but_cannot_write(): void
    {
        $this->actingAs($this->panelUser('kepsek@sekolah.id'));

        $this->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Panel Kepala Sekolah');

        $this->get(route('admin.students.index'))->assertOk();
        $this->get(route('admin.teachers.create'))->assertForbidden();
        $this->post(route('admin.announcements.store'), [
            'title' => 'Tidak boleh',
            'category' => 'berita',
            'body' => 'Isi',
        ])->assertForbidden();

        $this->get(route('admin.settings.edit'))->assertForbidden();
        $this->get(route('admin.users.index'))->assertForbidden();
    }

    public function test_panel_accounts_are_routed_to_admin_dashboard_after_login(): void
    {
        foreach ([['petugas@sekolah.id', 'petugas123'], ['kepsek@sekolah.id', 'kepsek123'], ['wakasek@sekolah.id', 'wakasek123']] as [$email, $password]) {
            $this->post('/login', ['email' => $email, 'password' => $password])
                ->assertRedirect('/dashboard');

            $this->get('/dashboard')->assertRedirect(route('admin.dashboard'));

            $this->post('/logout');
        }
    }
}
