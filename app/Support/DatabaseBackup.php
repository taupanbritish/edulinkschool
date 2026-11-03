<?php

namespace App\Support;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Symfony\Component\Process\Process;
use Throwable;

class DatabaseBackup
{
    private const DISK = 'backups';

    private const DIRECTORY = 'database';

    private const PATTERN = '/^[A-Za-z0-9][A-Za-z0-9._-]*\.dump$/';

    public function files(): Collection
    {
        $disk = Storage::disk(self::DISK);

        return collect($disk->files(self::DIRECTORY))
            ->map(fn (string $path) => [
                'name' => basename($path),
                'size' => $disk->size($path),
                'modified' => $disk->lastModified($path),
            ])
            ->sortByDesc('modified')
            ->values();
    }

    public function exists(string $name): bool
    {
        return preg_match(self::PATTERN, $name) === 1
            && Storage::disk(self::DISK)->exists(self::DIRECTORY.'/'.$name);
    }

    public function path(string $name): string
    {
        return self::DIRECTORY.'/'.$name;
    }

    public function create(string $prefix = 'sekolah'): string
    {
        $connection = config('database.connections.pgsql');
        $disk = Storage::disk(self::DISK);
        $name = $prefix.'-'.now()->format('Ymd-His').'.dump';
        $path = $this->path($name);
        $absolute = $disk->path($path);
        File::ensureDirectoryExists(dirname($absolute));

        $process = new Process(
            $this->dumpCommand($connection, $absolute),
            base_path(),
            filled($connection['password'] ?? null) ? ['PGPASSWORD' => $connection['password']] : [],
        );
        $process->setTimeout(300);

        try {
            $process->run();
        } catch (Throwable $e) {
            $disk->delete($path);

            throw new RuntimeException($e->getMessage(), 0, $e);
        }

        if (! $process->isSuccessful()) {
            $disk->delete($path);

            throw new RuntimeException($this->errorMessage($process));
        }

        $this->prune((int) config('backup.keep', 7));

        return $name;
    }

    /**
     * Pulihkan database dari berkas dump dan kembalikan nama backup pengaman.
     */
    public function restoreFile(string $absolute): string
    {
        if (! is_file($absolute)) {
            throw new RuntimeException('Berkas backup tidak ditemukan.');
        }

        @set_time_limit(0);
        ignore_user_abort(true);

        $connection = config('database.connections.pgsql');
        $lock = $this->lock();

        try {
            $this->assertRestorable($absolute);

            $putDown = ! app()->isDownForMaintenance();

            if ($putDown) {
                Artisan::call('down');
            }

            try {
                $safety = $this->create('sekolah-pengaman');
                $this->runRestore($connection, $absolute);
            } finally {
                if ($putDown) {
                    Artisan::call('up');
                }
            }
        } finally {
            flock($lock, LOCK_UN);
            fclose($lock);
        }

        return $safety;
    }

    public function delete(string $name): bool
    {
        return Storage::disk(self::DISK)->delete($this->path($name));
    }

    public function prune(int $keep): int
    {
        $disk = Storage::disk(self::DISK);

        $stale = collect($disk->files(self::DIRECTORY))
            ->filter(fn (string $path) => preg_match(self::PATTERN, basename($path)) === 1)
            ->sortByDesc(fn (string $path) => $disk->lastModified($path))
            ->slice(max($keep, 1));

        foreach ($stale as $path) {
            $disk->delete($path);
        }

        return $stale->count();
    }

    private function dumpCommand(array $connection, string $absolute): array
    {
        return [
            $this->binary('pg_dump'),
            '--format=custom',
            '--no-owner',
            '--no-privileges',
            '--host='.$connection['host'],
            '--port='.$connection['port'],
            '--username='.$connection['username'],
            '--no-password',
            '--file='.$absolute,
            $connection['database'],
        ];
    }

    private function restoreCommand(array $connection, string $absolute): array
    {
        return [
            $this->binary('pg_restore'),
            '--clean',
            '--if-exists',
            '--no-owner',
            '--no-privileges',
            '--single-transaction',
            '--host='.$connection['host'],
            '--port='.$connection['port'],
            '--username='.$connection['username'],
            '--no-password',
            '--dbname='.$connection['database'],
            $absolute,
        ];
    }

    private function assertRestorable(string $absolute): void
    {
        $process = new Process([$this->binary('pg_restore'), '--list', $absolute], base_path());
        $process->setTimeout(60);

        try {
            $process->run();
        } catch (Throwable $e) {
            throw new RuntimeException('pg_restore tidak dapat dijalankan: '.$e->getMessage(), 0, $e);
        }

        if (! $process->isSuccessful()) {
            throw new RuntimeException('Berkas backup tidak valid dan tidak dipulihkan. '.$this->errorMessage($process, 'pg_restore'));
        }
    }

    private function runRestore(array $connection, string $absolute): void
    {
        $process = new Process(
            $this->restoreCommand($connection, $absolute),
            base_path(),
            filled($connection['password'] ?? null) ? ['PGPASSWORD' => $connection['password']] : [],
        );
        $process->setTimeout((int) config('backup.restore_timeout', 600));

        try {
            $process->run();
        } catch (Throwable $e) {
            throw new RuntimeException('pg_restore tidak dapat dijalankan: '.$e->getMessage(), 0, $e);
        }

        if (! $process->isSuccessful()) {
            throw new RuntimeException($this->errorMessage($process, 'pg_restore'));
        }
    }

    private function binary(string $tool): string
    {
        $configured = config('database.connections.pgsql.'.($tool === 'pg_restore' ? 'restore_binary' : 'dump_binary'));

        if (filled($configured)) {
            return $configured;
        }

        $candidates = array_filter([
            '/opt/homebrew/bin/'.$tool,
            '/usr/local/bin/'.$tool,
            '/usr/bin/'.$tool,
            '/Applications/Postgres.app/Contents/Versions/latest/bin/'.$tool,
            ...(glob('/Library/PostgreSQL/*/bin/'.$tool) ?: []),
        ]);

        foreach ($candidates as $candidate) {
            if (is_executable($candidate)) {
                return $candidate;
            }
        }

        return $tool;
    }

    private function errorMessage(Process $process, string $tool = 'pg_dump'): string
    {
        $message = trim($process->getErrorOutput()) ?: trim($process->getOutput());

        if ($message === '') {
            $variable = $tool === 'pg_restore' ? 'DB_RESTORE_BINARY' : 'DB_DUMP_BINARY';

            return $tool.' keluar dengan kode '.$process->getExitCode().'. Pastikan '.$variable.' menunjuk ke '.$tool.' yang sesuai.';
        }

        return $tool.' gagal: '.mb_strimwidth($message, 0, 300, '…');
    }

    /**
     * @return resource
     */
    private function lock()
    {
        $directory = storage_path('framework');
        File::ensureDirectoryExists($directory);

        $handle = fopen($directory.'/database-restore.lock', 'c');

        if ($handle === false) {
            throw new RuntimeException('Berkas kunci restore tidak dapat dibuat.');
        }

        if (! flock($handle, LOCK_EX | LOCK_NB)) {
            fclose($handle);

            throw new RuntimeException('Proses restore lain sedang berjalan. Coba lagi setelah selesai.');
        }

        return $handle;
    }
}
