<?php

namespace App\Support;

use Illuminate\Support\Collection;
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

    public function create(): string
    {
        $connection = config('database.connections.pgsql');
        $disk = Storage::disk(self::DISK);
        $name = 'sekolah-'.now()->format('Ymd-His').'.dump';
        $path = $this->path($name);
        $absolute = $disk->path($path);
        File::ensureDirectoryExists(dirname($absolute));

        $process = new Process(
            $this->command($connection, $absolute),
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

    private function command(array $connection, string $absolute): array
    {
        $command = [
            $this->binary(),
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

        return $command;
    }

    private function binary(): string
    {
        $configured = config('database.connections.pgsql.dump_binary');

        if (filled($configured)) {
            return $configured;
        }

        $candidates = array_filter([
            '/opt/homebrew/bin/pg_dump',
            '/usr/local/bin/pg_dump',
            '/usr/bin/pg_dump',
            '/Applications/Postgres.app/Contents/Versions/latest/bin/pg_dump',
            ...(glob('/Library/PostgreSQL/*/bin/pg_dump') ?: []),
        ]);

        foreach ($candidates as $candidate) {
            if (is_executable($candidate)) {
                return $candidate;
            }
        }

        return 'pg_dump';
    }

    private function errorMessage(Process $process): string
    {
        $message = trim($process->getErrorOutput()) ?: trim($process->getOutput());

        if ($message === '') {
            return 'pg_dump keluar dengan kode '.$process->getExitCode().'. Pastikan DB_DUMP_BINARY menunjuk ke pg_dump yang sesuai.';
        }

        return 'pg_dump gagal: '.mb_strimwidth($message, 0, 300, '…');
    }
}
