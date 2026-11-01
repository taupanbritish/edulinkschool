@extends('layouts.admin')

@section('title', 'Berita & Pengumuman')

@section('content')
<div class="page-head">
    <div>
        <h1>Berita &amp; Pengumuman</h1>
        <p>Konten yang tampil di website sekolah dan aplikasi mobile.</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('admin.announcements.create') }}" class="btn btn-primary"><x-icon name="plus" size="15px" /> Tulis Baru</a>
    </div>
</div>

<div class="panel">
    <div class="panel-head">
        <form method="GET" action="{{ route('admin.announcements.index') }}" class="filter-bar">
            <input type="search" name="q" value="{{ request('q') }}" class="input" placeholder="Cari judul…">
            <select name="category" class="select">
                <option value="">Semua kategori</option>
                @foreach(\App\Models\Announcement::CATEGORIES as $value => $label)
                    <option value="{{ $value }}" @selected(request('category') === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-outline btn-sm"><x-icon name="search" size="14px" /> Cari</button>
        </form>
        <span class="muted" style="font-size:.8rem">{{ $announcements->total() }} artikel</span>
    </div>
    <div class="panel-body flush">
        <div class="table-wrap">
            <table class="data">
                <thead>
                    <tr><th>Judul</th><th>Kategori</th><th>Status</th><th>Terbit</th><th>Penulis</th><th style="text-align:right">Aksi</th></tr>
                </thead>
                <tbody>
                    @forelse($announcements as $announcement)
                        <tr>
                            <td>
                                <span class="cell-main">{{ $announcement->title }}</span>
                                <span class="cell-sub">{{ \Illuminate\Support\Str::limit($announcement->excerpt, 80) }}</span>
                                @if($announcement->attachments_count > 0)
                                    <span class="cell-sub">{{ $announcement->attachments_count }} lampiran galeri</span>
                                @endif
                            </td>
                            <td><span class="badge {{ $announcement->category === 'pengumuman' ? 'badge-announcement' : 'badge-news' }}">{{ $announcement->categoryLabel() }}</span></td>
                            <td><span class="badge {{ $announcement->is_published ? 'badge-success' : 'badge-neutral' }}">{{ $announcement->is_published ? 'Terbit' : 'Draf' }}</span></td>
                            <td>{{ $announcement->published_at?->locale('id')->translatedFormat('d M Y') ?? '-' }}</td>
                            <td>{{ $announcement->author?->name ?? '-' }}</td>
                            <td>
                                <div class="row-actions">
                                    @if($announcement->is_published)
                                        <a href="{{ route('news.show', $announcement) }}" target="_blank" rel="noopener" class="btn btn-sm btn-ghost"><x-icon name="arrow-right" size="13px" /></a>
                                    @endif
                                    <a href="{{ route('admin.announcements.edit', $announcement) }}" class="btn btn-sm btn-outline"><x-icon name="edit" size="13px" /> Ubah</a>
                                    <form method="POST" action="{{ route('admin.announcements.destroy', $announcement) }}" class="inline-form" onsubmit="return confirm('Hapus artikel ini?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger"><x-icon name="trash" size="13px" /></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6"><div class="empty-state">Belum ada artikel.</div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @include('partials.pagination', ['paginator' => $announcements])
    </div>
</div>
@endsection
