@extends('layouts.admin')

@section('title', 'Restore Database')

@section('content')
<div class="page-head">
    <div>
        <h1>Restore Database</h1>
        <p>Pulihkan seluruh data dari berkas backup yang telah Anda unduh.</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('admin.backups.index') }}" class="btn btn-outline"><x-icon name="download" size="15px" /> Halaman Backup</a>
    </div>
</div>

@include('admin.partials.settings-tabs')

<div class="restore-grid">
    <div class="panel">
        <div class="panel-head">
            <h2>Unggah berkas backup</h2>
        </div>
        <div class="panel-body">
            <ol class="restore-steps">
                <li>Buka <strong>Backup Database</strong>, lalu unduh berkas <code>.dump</code> yang ingin dipulihkan.</li>
                <li>Unggah berkas tersebut di sini, lalu ketik <strong>PULIHKAN</strong>.</li>
                <li>Sistem membuat backup pengaman, mengaktifkan mode pemeliharaan, dan mengeluarkan semua pengguna dari panel.</li>
            </ol>

            <form method="POST" action="{{ route('admin.restore.store') }}" enctype="multipart/form-data" id="restoreUploadForm">
                @csrf

                <div class="field">
                    <label for="backupFile">Berkas backup (.dump)</label>
                    <input class="input" id="backupFile" name="backup" type="file" accept=".dump" required
                        data-restore-file-input>
                    <span class="hint">Berkas hasil unduhan halaman Backup Database. Maksimal {{ $uploadMaxMb }} MB (mengikuti batas unggah PHP server).</span>
                </div>

                <div class="field">
                    <label for="restoreConfirmation">Ketik <strong>PULIHKAN</strong> untuk melanjutkan</label>
                    <input class="input" id="restoreConfirmation" name="confirmation" type="text" autocomplete="off"
                        autocapitalize="characters" spellcheck="false" required>
                </div>

                <div class="restore-warning">
                    <strong>Data saat ini akan ditimpa.</strong>
                    Pastikan berkas berasal dari sekolah ini. Foto dan media unggahan tidak termasuk dalam restore.
                </div>

                <button type="submit" class="btn btn-danger restore-submit" data-restore-submit disabled>
                    <x-icon name="refresh" size="15px" /> Pulihkan database
                </button>
            </form>
        </div>
    </div>

    <aside class="panel">
        <div class="panel-head">
            <h2>Sebelum memulai</h2>
        </div>
        <div class="panel-body">
            <ul class="restore-notes">
                <li>Hanya berkas <code>.dump</code> format PostgreSQL custom yang diterima.</li>
                <li>Berkas divalidasi dengan <code>pg_restore</code> sebelum database disentuh.</li>
                <li>Backup pengaman dibuat otomatis dan muncul di daftar backup.</li>
                <li>Proses dapat memakan waktu beberapa menit. Jangan tutup halaman sampai selesai.</li>
                <li>Setelah berhasil, semua pengguna panel dikeluarkan dan harus masuk kembali.</li>
            </ul>
            <p class="restore-note-foot">Butuh berkas backup? <a href="{{ route('admin.backups.index') }}">Buka Backup Database</a>.</p>
        </div>
    </aside>
</div>
@endsection
