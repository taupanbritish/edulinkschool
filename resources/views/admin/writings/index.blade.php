@extends('layouts.admin')

@section('title', 'Majalah / Artikel Siswa')

@section('content')
<div class="page-head">
    <div>
        <h1>Majalah / Artikel Siswa</h1>
        <p>Karya tulis, cerpen, dan opini siswa serta guru yang tampil di website.</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('writings.index') }}" target="_blank" rel="noopener" class="btn btn-outline btn-sm">Lihat halaman</a>
        <a href="{{ route('admin.writings.create') }}" class="btn btn-primary"><x-icon name="plus" size="15px" /> Tulis Karya Baru</a>
    </div>
</div>

<div class="panel">
    <div class="panel-head">
        <form method="GET" action="{{ route('admin.writings.index') }}" class="filter-bar">
            <input type="search" name="q" value="{{ request('q') }}" class="input" placeholder="Cari judul atau penulis…">
            <select name="category" class="select">
                <option value="">Semua kategori</option>
                @foreach(\App\Models\StudentWriting::CATEGORIES as $value => $label)
                    <option value="{{ $value }}" @selected(request('category') === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-outline btn-sm"><x-icon name="search" size="14px" /> Cari</button>
        </form>
        <span class="muted" style="font-size:.8rem">{{ $writings->total() }} karya</span>
    </div>
    <div class="panel-body flush">
        <div class="table-wrap">
            <table class="data">
                <thead>
                    <tr><th>Judul</th><th>Kategori</th><th>Penulis</th><th>Status</th><th>Terbit</th><th style="text-align:right">Aksi</th></tr>
                </thead>
                <tbody>
                    @forelse($writings as $writing)
                        <tr>
                            <td>
                                <span class="cell-main">{{ $writing->title }}</span>
                                <span class="cell-sub">{{ \Illuminate\Support\Str::limit($writing->excerpt, 80) }}</span>
                            </td>
                            <td><span class="badge badge-news">{{ $writing->categoryLabel() }}</span></td>
                            <td>
                                <span class="cell-main">{{ $writing->author_name }}</span>
                                <span class="cell-sub">{{ $writing->authorTypeLabel() }}</span>
                            </td>
                            <td><span class="badge {{ $writing->is_published ? 'badge-success' : 'badge-neutral' }}">{{ $writing->is_published ? 'Terbit' : 'Draf' }}</span></td>
                            <td>{{ $writing->published_at?->locale('id')->translatedFormat('d M Y') ?? '-' }}</td>
                            <td>
                                <div class="row-actions">
                                    @if($writing->is_published)
                                        <a href="{{ route('writings.show', $writing) }}" target="_blank" rel="noopener" class="btn btn-sm btn-ghost"><x-icon name="arrow-right" size="13px" /></a>
                                    @endif
                                    <a href="{{ route('admin.writings.edit', $writing) }}" class="btn btn-sm btn-outline"><x-icon name="edit" size="13px" /> Ubah</a>
                                    <form method="POST" action="{{ route('admin.writings.destroy', $writing) }}" class="inline-form" onsubmit="return confirm('Hapus artikel ini?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger"><x-icon name="trash" size="13px" /></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6"><div class="empty-state">Belum ada artikel siswa.</div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @include('partials.pagination', ['paginator' => $writings])
    </div>
</div>
@endsection
