<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DatabaseBackupTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('backups');
        $this->seed(DatabaseSeeder::class);
    }

    private function admin(): User
    {
        return User::where('email', 'admin@sekolah.id')->firstOrFail();
    }

    public function test_admin_page_lists_backup_files(): void
    {
        Storage::disk('backups')->put('database/sekolah-20261101-090000.dump', 'dump-content');

        $this->actingAs($this->admin())
            ->get(route('admin.backups.index'))
            ->assertOk()
            ->assertSee('Backup Database')
            ->assertSee('sekolah-20261101-090000.dump')
            ->assertSee('1 dari 7 berkas')
            ->assertSee('Unduh');
    }

    public function test_admin_page_shows_empty_state(): void
    {
        $this->actingAs($this->admin())
            ->get(route('admin.backups.index'))
            ->assertOk()
            ->assertSee('Belum ada berkas backup');
    }

    public function test_admin_creates_backup_with_pg_dump(): void
    {
        config(['database.connections.pgsql.dump_binary' => $this->fakeDumpBinary()]);

        $this->actingAs($this->admin())
            ->post(route('admin.backups.store'))
            ->assertRedirect(route('admin.backups.index'))
            ->assertSessionHas('success');

        $files = Storage::disk('backups')->files('database');

        $this->assertCount(1, $files);
        $this->assertStringStartsWith('sekolah-', basename($files[0]));
        $this->assertStringEndsWith('.dump', $files[0]);
        $this->assertSame('fake-dump', Storage::disk('backups')->get($files[0]));
    }

    public function test_admin_can_download_backup(): void
    {
        Storage::disk('backups')->put('database/sekolah-20261101-090000.dump', 'dump-content');

        $this->actingAs($this->admin())
            ->get(route('admin.backups.download', 'sekolah-20261101-090000.dump'))
            ->assertOk()
            ->assertDownload('sekolah-20261101-090000.dump')
            ->assertStreamedContent('dump-content');
    }

    public function test_admin_can_delete_backup(): void
    {
        Storage::disk('backups')->put('database/sekolah-20261101-090000.dump', 'dump-content');

        $this->actingAs($this->admin())
            ->delete(route('admin.backups.destroy', 'sekolah-20261101-090000.dump'))
            ->assertRedirect(route('admin.backups.index'))
            ->assertSessionHas('success');

        Storage::disk('backups')->assertMissing('database/sekolah-20261101-090000.dump');
    }

    public function test_download_returns_not_found_for_missing_file(): void
    {
        $this->actingAs($this->admin())
            ->get(route('admin.backups.download', 'sekolah-20260101-000000.dump'))
            ->assertNotFound();
    }

    public function test_create_reports_error_when_pg_dump_is_missing(): void
    {
        config(['database.connections.pgsql.dump_binary' => '/nonexistent/pg_dump-binary']);

        $this->actingAs($this->admin())
            ->post(route('admin.backups.store'))
            ->assertRedirect(route('admin.backups.index'))
            ->assertSessionHas('error');

        $this->assertSame([], Storage::disk('backups')->allFiles('database'));
    }

    public function test_daily_backup_command_prunes_files_beyond_retention(): void
    {
        config([
            'database.connections.pgsql.dump_binary' => $this->fakeDumpBinary(),
            'backup.keep' => 7,
        ]);

        $disk = Storage::disk('backups');

        for ($i = 1; $i <= 7; $i++) {
            $name = 'sekolah-202610'.str_pad((string) $i, 2, '0', STR_PAD_LEFT).'-020000.dump';
            $disk->put('database/'.$name, 'old-'.$i);
            touch($disk->path('database/'.$name), now()->subDays(8 - $i)->timestamp);
        }

        $this->artisan('backup:database')
            ->expectsOutputToContain('retensi 7')
            ->assertSuccessful();

        $files = collect($disk->files('database'));

        $this->assertCount(7, $files);
        $disk->assertMissing('database/sekolah-20261001-020000.dump');

        $fresh = $files->first(fn (string $path) => str_starts_with(basename($path), 'sekolah-'.now()->format('Ymd')));
        $this->assertNotNull($fresh);
        $this->assertSame('fake-dump', $disk->get($fresh));
    }

    public function test_daily_backup_command_fails_cleanly_when_pg_dump_is_missing(): void
    {
        config(['database.connections.pgsql.dump_binary' => '/nonexistent/pg_dump-binary']);

        $this->artisan('backup:database')
            ->expectsOutputToContain('Backup gagal dibuat')
            ->assertExitCode(1);

        $this->assertSame([], Storage::disk('backups')->allFiles('database'));
    }

    public function test_daily_backup_is_scheduled_at_configured_time(): void
    {
        config(['backup.daily_at' => '02:00']);

        $this->artisan('schedule:list')->assertSuccessful();

        $event = collect(app(Schedule::class)->events())
            ->first(fn ($event) => str_contains($event->command, 'backup:database'));

        $this->assertNotNull($event);
        $this->assertSame('0 2 * * *', $event->expression);
    }

    public function test_petugas_cannot_access_database_backup(): void
    {
        $petugas = User::where('email', 'petugas@sekolah.id')->firstOrFail();

        $this->actingAs($petugas)
            ->get(route('admin.backups.index'))
            ->assertForbidden();

        $this->actingAs($petugas)
            ->post(route('admin.backups.store'))
            ->assertForbidden();
    }

    private function fakeDumpBinary(): string
    {
        $path = storage_path('framework/testing/pg_dump-stub');
        File::ensureDirectoryExists(dirname($path));
        File::put($path, <<<'SH'
#!/bin/sh
out=""
for arg in "$@"; do
  case "$arg" in
    --file=*) out="${arg#--file=}" ;;
  esac
done
printf 'fake-dump' > "$out"
SH);
        chmod($path, 0755);

        $this->beforeApplicationDestroyed(fn () => File::delete($path));

        return $path;
    }
}
