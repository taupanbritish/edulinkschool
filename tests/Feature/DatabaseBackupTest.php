<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
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
            ->assertSee('Unduh')
            ->assertSee('Restore Database')
            ->assertDontSee('data-restore-open', false)
            ->assertDontSee('id="restoreDialog"', false);
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

    public function test_backup_and_restore_are_grouped_under_settings(): void
    {
        $admin = $this->admin();

        foreach ([route('admin.settings.edit'), route('admin.backups.index'), route('admin.restore.form')] as $url) {
            $this->actingAs($admin)
                ->get($url)
                ->assertOk()
                ->assertSee('settings-tabs', false)
                ->assertSee('Pengaturan Sekolah')
                ->assertSee('Backup Database')
                ->assertSee('Restore Database');
        }
    }

    public function test_restore_page_is_shown_separately(): void
    {
        $this->actingAs($this->admin())
            ->get(route('admin.restore.form'))
            ->assertOk()
            ->assertSee('Restore Database')
            ->assertSee('Unggah berkas backup')
            ->assertSee('PULIHKAN')
            ->assertSee('Maksimal')
            ->assertSee('enctype="multipart/form-data"', false);
    }

    public function test_restore_requires_confirmation_word(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.restore.store'), [
                'backup' => $this->fakeUpload(),
                'confirmation' => 'HAPUS',
            ])
            ->assertRedirect(route('admin.restore.form'))
            ->assertSessionHas('error');

        $this->assertAuthenticated();
        $this->assertSame([], Storage::disk('backups')->allFiles('restore'));
        $this->assertSame([], Storage::disk('backups')->allFiles('database'));
        $this->assertFalse(app()->isDownForMaintenance());
    }

    public function test_restore_requires_an_uploaded_dump(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.restore.store'), ['confirmation' => 'PULIHKAN'])
            ->assertSessionHasErrors('backup');

        $this->actingAs($this->admin())
            ->post(route('admin.restore.store'), [
                'backup' => UploadedFile::fake()->create('backup.sql', 10),
                'confirmation' => 'PULIHKAN',
            ])
            ->assertSessionHasErrors('backup');

        $this->assertAuthenticated();
        $this->assertSame([], Storage::disk('backups')->allFiles('restore'));
    }

    public function test_restore_creates_safety_backup_and_restores_uploaded_dump(): void
    {
        [$restoreBinary, $log] = $this->fakeRestoreBinary();

        config([
            'database.connections.pgsql.dump_binary' => $this->fakeDumpBinary(),
            'database.connections.pgsql.restore_binary' => $restoreBinary,
        ]);

        $this->actingAs($this->admin())
            ->post(route('admin.restore.store'), [
                'backup' => $this->fakeUpload('sekolah-20261101-090000.dump'),
                'confirmation' => 'PULIHKAN',
            ])
            ->assertRedirect(route('login'))
            ->assertSessionHas('restore_success', fn (array $flash) => $flash['file'] === 'sekolah-20261101-090000.dump');

        $this->assertGuest();
        $this->assertFalse(app()->isDownForMaintenance());

        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Berhasil Restore Database')
            ->assertSee('sekolah-20261101-090000.dump');

        $files = collect(Storage::disk('backups')->files('database'))->map(fn ($path) => basename($path));
        $safety = $files->first(fn (string $name) => str_starts_with($name, 'sekolah-pengaman-'));

        $this->assertNotNull($safety);
        $this->assertCount(1, $files);
        $this->assertSame('fake-dump', Storage::disk('backups')->get('database/'.$safety));
        $this->assertSame([], Storage::disk('backups')->allFiles('restore'));

        $args = File::get($log);
        $this->assertStringContainsString('--list', $args);
        $this->assertStringContainsString('--clean', $args);
        $this->assertStringContainsString('--if-exists', $args);
        $this->assertStringContainsString('--single-transaction', $args);
        $this->assertStringContainsString('--dbname='.config('database.connections.pgsql.database'), $args);
        $this->assertStringContainsString('/restore/unggahan-', $args);
    }

    public function test_restore_failure_keeps_database_online_and_user_signed_in(): void
    {
        [$restoreBinary, $log] = $this->fakeRestoreBinary(fail: true);

        config([
            'database.connections.pgsql.dump_binary' => $this->fakeDumpBinary(),
            'database.connections.pgsql.restore_binary' => $restoreBinary,
        ]);

        $this->actingAs($this->admin())
            ->post(route('admin.restore.store'), [
                'backup' => $this->fakeUpload(),
                'confirmation' => 'PULIHKAN',
            ])
            ->assertRedirect(route('admin.restore.form'))
            ->assertSessionHas('error');

        $this->assertAuthenticated();
        $this->assertFalse(app()->isDownForMaintenance());

        $files = collect(Storage::disk('backups')->files('database'))->map(fn ($path) => basename($path));
        $this->assertTrue($files->contains(fn (string $name) => str_starts_with($name, 'sekolah-pengaman-')));
        $this->assertSame([], Storage::disk('backups')->allFiles('restore'));
        $this->assertStringContainsString('--clean', File::get($log));
    }

    public function test_restore_deletes_upload_when_dump_is_invalid(): void
    {
        [$restoreBinary, $log] = $this->fakeRestoreBinary(failList: true);

        config([
            'database.connections.pgsql.dump_binary' => $this->fakeDumpBinary(),
            'database.connections.pgsql.restore_binary' => $restoreBinary,
        ]);

        $this->actingAs($this->admin())
            ->post(route('admin.restore.store'), [
                'backup' => $this->fakeUpload('tidak-valid.dump'),
                'confirmation' => 'PULIHKAN',
            ])
            ->assertRedirect(route('admin.restore.form'))
            ->assertSessionHas('error', fn (string $message) => str_contains($message, 'tidak valid'));

        $this->assertAuthenticated();
        $this->assertFalse(app()->isDownForMaintenance());
        $this->assertSame([], Storage::disk('backups')->allFiles('database'));
        $this->assertSame([], Storage::disk('backups')->allFiles('restore'));
        $this->assertStringContainsString('--list', File::get($log));
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

        $this->actingAs($petugas)
            ->get(route('admin.restore.form'))
            ->assertForbidden();

        $this->actingAs($petugas)
            ->post(route('admin.restore.store'), [
                'backup' => $this->fakeUpload(),
                'confirmation' => 'PULIHKAN',
            ])
            ->assertForbidden();
    }

    private function fakeUpload(string $name = 'sekolah-20261101-090000.dump'): UploadedFile
    {
        return UploadedFile::fake()->createWithContent($name, 'dump-content');
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function fakeRestoreBinary(bool $fail = false, bool $failList = false): array
    {
        $directory = storage_path('framework/testing');
        File::ensureDirectoryExists($directory);

        $suffix = $failList ? 'invalid' : ($fail ? 'fail' : 'ok');
        $path = $directory.'/pg_restore-stub-'.$suffix;
        $log = $directory.'/pg_restore-log-'.$suffix;
        File::put($log, '');

        $script = $failList
            ? <<<'SH'
#!/bin/sh
log="__LOG__"
for arg in "$@"; do
  printf '%s\n' "$arg" >> "$log"
done
echo "pg_restore: error: input file does not appear to be a valid archive" >&2
exit 1
SH
            : ($fail
                ? <<<'SH'
#!/bin/sh
log="__LOG__"
for arg in "$@"; do
  printf '%s\n' "$arg" >> "$log"
  [ "$arg" = "--list" ] && exit 0
done
echo "pg_restore: error: simulasi gagal" >&2
exit 1
SH
                : <<<'SH'
#!/bin/sh
log="__LOG__"
for arg in "$@"; do
  printf '%s\n' "$arg" >> "$log"
done
exit 0
SH);

        File::put($path, str_replace('__LOG__', $log, $script));
        chmod($path, 0755);

        $this->beforeApplicationDestroyed(fn () => File::delete([$path, $log]));

        return [$path, $log];
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
