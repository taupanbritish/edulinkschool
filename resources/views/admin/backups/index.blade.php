@extends('layouts.admin')

@section('title', 'Backup Database')

@section('content')
<div class="page-head">
    <div>
        <h1>Backup Database</h1>
        <p>Buat salinan penuh database PostgreSQL, unduh, atau hapus berkas lama.</p>
    </div>
    <div class="page-actions">
        <form method="POST" action="{{ route('admin.backups.store') }}" onsubmit="this.querySelector('button').disabled = true;">
            @csrf
            <button type="submit" class="btn btn-primary"><x-icon name="download" size="15px" /> Buat Backup Sekarang</button>
        </form>
    </div>
</div>

<div class="panel">
    <div class="panel-head">
        <h2>Berkas backup</h2>
        <span class="muted" style="font-size:.8rem">{{ $files->count() }} dari {{ $keep }} berkas</span>
    </div>
    <div class="panel-body flush">
        <div class="table-wrap">
            <table class="data">
                <thead>
                    <tr><th>Nama berkas</th><th>Ukuran</th><th>Dibuat</th><th style="text-align:right">Aksi</th></tr>
                </thead>
                <tbody>
                    @forelse($files as $file)
                        @php
                            $size = $file['size'] >= 1048576
                                ? number_format($file['size'] / 1048576, 2, ',', '.').' MB'
                                : number_format($file['size'] / 1024, 1, ',', '.').' KB';
                        @endphp
                        <tr>
                            <td>
                                <span class="cell-main">{{ $file['name'] }}</span>
                                <span class="cell-sub">Format PostgreSQL custom (.dump)</span>
                            </td>
                            <td>{{ $size }}</td>
                            <td>{{ now()->setTimestamp($file['modified'])->locale('id')->translatedFormat('d M Y, H:i') }}</td>
                            <td>
                                <div class="row-actions">
                                    <a href="{{ route('admin.backups.download', $file['name']) }}" class="btn btn-sm btn-outline"><x-icon name="download" size="13px" /> Unduh</a>
                                    <form method="POST" action="{{ route('admin.backups.destroy', $file['name']) }}" class="inline-form" onsubmit="return confirm('Hapus berkas backup {{ $file['name'] }}?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger"><x-icon name="trash" size="13px" /></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4"><div class="empty-state">Belum ada berkas backup. Klik "Buat Backup Sekarang" untuk memulai.</div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<p class="muted" style="font-size:.82rem;margin-top:1rem">
    Backup otomatis berjalan setiap hari pukul {{ $dailyAt }} dan hanya {{ $keep }} berkas terbaru yang disimpan.
    Berkas disimpan di penyimpanan privat server dan hanya dapat diunduh oleh administrator.
    Backup memuat seluruh tabel database; foto dan media unggahan tidak termasuk.
</p>
@endsection
