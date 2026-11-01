<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\DatabaseBackup;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
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
}
