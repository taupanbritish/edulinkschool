<?php

use App\Support\DatabaseBackup;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('backup:database', function () {
    $backups = app(DatabaseBackup::class);

    try {
        $name = $backups->create();
    } catch (Throwable $e) {
        report($e);
        $this->error('Backup gagal dibuat: '.$e->getMessage());

        return 1;
    }

    $this->info('Backup dibuat: '.$name);
    $this->line('Total berkas tersimpan: '.$backups->files()->count().' (retensi '.config('backup.keep').').');

    return 0;
})->purpose('Membuat backup database PostgreSQL dan merapikan berkas lama.');

Schedule::command('backup:database')
    ->dailyAt(config('backup.daily_at'))
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/backup.log'));
