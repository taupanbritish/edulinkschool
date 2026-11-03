<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\DatabaseBackup;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class DatabaseBackupController extends Controller
{
    public function __construct(private DatabaseBackup $backups) {}

    public function index(): View
    {
        return view('admin.backups.index', [
            'files' => $this->backups->files(),
            'keep' => (int) config('backup.keep', 7),
            'dailyAt' => config('backup.daily_at'),
        ]);
    }

    public function restoreForm(): View
    {
        return view('admin.restore.form', [
            'uploadMaxMb' => max(1, (int) floor(min(
                (int) config('backup.upload_max', 512000),
                $this->phpLimitKb('upload_max_filesize'),
                $this->phpLimitKb('post_max_size'),
            ) / 1024)),
        ]);
    }

    private function phpLimitKb(string $key): int
    {
        $value = trim((string) ini_get($key));

        if ($value === '' || $value === '-1') {
            return PHP_INT_MAX;
        }

        $number = (int) $value;

        if ($number <= 0) {
            return PHP_INT_MAX;
        }

        return match (strtolower(substr($value, -1))) {
            'g' => $number * 1024 * 1024,
            'm' => $number * 1024,
            'k' => $number,
            default => (int) round($number / 1024),
        };
    }

    public function store(): RedirectResponse
    {
        try {
            $name = $this->backups->create();
        } catch (Throwable $e) {
            report($e);

            return redirect()->route('admin.backups.index')->with('error', 'Backup gagal dibuat. '.$e->getMessage());
        }

        return redirect()->route('admin.backups.index')->with('success', 'Backup database '.$name.' berhasil dibuat.');
    }

    public function download(string $file): StreamedResponse
    {
        abort_unless($this->backups->exists($file), 404);

        return Storage::disk('backups')->download($this->backups->path($file), $file);
    }

    public function destroy(string $file): RedirectResponse
    {
        abort_unless($this->backups->exists($file), 404);

        $this->backups->delete($file);

        return redirect()->route('admin.backups.index')->with('success', 'Berkas backup '.$file.' dihapus.');
    }

    public function restore(Request $request): RedirectResponse
    {
        $request->validate([
            'backup' => ['required', 'file', 'extensions:dump', 'max:'.(int) config('backup.upload_max', 512000)],
            'confirmation' => ['required', 'string'],
        ], [
            'backup.required' => 'Pilih berkas backup (.dump) yang akan dipulihkan.',
            'backup.extensions' => 'Berkas harus berekstensi .dump hasil unduhan halaman Backup Database.',
            'backup.max' => 'Ukuran berkas melebihi batas unggah.',
            'confirmation.required' => 'Ketik PULIHKAN untuk melanjutkan.',
        ]);

        if (mb_strtoupper(trim((string) $request->input('confirmation'))) !== 'PULIHKAN') {
            return redirect()->route('admin.restore.form')
                ->with('error', 'Restore dibatalkan: ketik PULIHKAN untuk melanjutkan.');
        }

        $upload = $request->file('backup');
        $original = mb_strimwidth(basename($upload->getClientOriginalName()), 0, 120, '…');
        $disk = Storage::disk('backups');
        $path = 'restore/unggahan-'.now()->format('Ymd-His').'-'.Str::lower(Str::random(8)).'.dump';

        if (! $upload->storeAs('restore', basename($path), 'backups')) {
            return redirect()->route('admin.restore.form')
                ->with('error', 'Berkas unggahan tidak dapat disimpan. Periksa izin folder penyimpanan.');
        }

        try {
            $safety = $this->backups->restoreFile($disk->path($path));
        } catch (Throwable $e) {
            report($e);

            return redirect()->route('admin.restore.form')
                ->with('error', 'Restore database gagal. '.$e->getMessage());
        } finally {
            $disk->delete($path);
        }

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('restore_success', [
                'file' => $original,
                'safety' => $safety,
            ]);
    }
}
