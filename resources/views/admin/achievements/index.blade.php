@extends('layouts.admin')

@section('title', 'Prestasi')

@section('content')
<div class="page-head">
    <div>
        <h1>Prestasi</h1>
        <p>Kelola prestasi akademik dan non akademik yang tampil di website sekolah.</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('admin.achievements.create') }}" class="btn btn-primary"><x-icon name="plus" size="15px" /> Tambah Prestasi</a>
    </div>
</div>

<div class="panel">
    <div class="panel-head">
        <form method="GET" action="{{ route('admin.achievements.index') }}" class="filter-bar">
            <input type="search" name="q" value="{{ request('q') }}" class="input" placeholder="Cari judul atau penerima…">
            <select name="category" class="select">
                <option value="">Semua kategori</option>
                @foreach(\App\Models\Achievement::CATEGORIES as $value => $label)
                    <option value="{{ $value }}" @selected(request('category') === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-outline btn-sm"><x-icon name="search" size="14px" /> Cari</button>
            @if(request('q') || request('category'))
                <a href="{{ route('admin.achievements.index') }}" class="btn btn-ghost btn-sm">Reset</a>
            @endif
        </form>
        <span class="muted" style="font-size:.8rem">{{ $achievements->total() }} data</span>
    </div>
    <div class="panel-body flush">
        <div class="table-wrap">
            <table class="data">
                <thead>
                    <tr><th>Prestasi</th><th>Kategori</th><th>Penerima</th><th>Tingkat</th><th>Tanggal</th><th>Status</th><th style="text-align:right">Aksi</th></tr>
                </thead>
                <tbody>
                    @forelse($achievements as $achievement)
                        <tr>
                            <td>
                                <div class="cell-photo">
                                    @if($achievement->photoUrl())
                                        <img src="{{ $achievement->photoUrl() }}" alt="" class="thumb">
                                    @else
                                        <span class="thumb thumb-initial">{{ strtoupper(substr($achievement->title, 0, 1)) }}</span>
                                    @endif
                                    <div>
                                        <span class="cell-main">{{ $achievement->title }}</span>
                                        <span class="cell-sub">{{ $achievement->description ? \Illuminate\Support\Str::limit($achievement->description, 80) : 'Tanpa keterangan' }}</span>
                                    </div>
                                </div>
                            </td>
                            <td><span class="badge {{ $achievement->category === 'akademik' ? 'badge-news' : 'badge-announcement' }}">{{ $achievement->categoryLabel() }}</span></td>
                            <td>{{ $achievement->recipient ?? '-' }}</td>
                            <td>{{ $achievement->level ?? '-' }}</td>
                            <td>{{ $achievement->achieved_at?->locale('id')->translatedFormat('d M Y') ?? '-' }}</td>
                            <td><span class="badge {{ $achievement->is_active ? 'badge-success' : 'badge-neutral' }}">{{ $achievement->is_active ? 'Tampil' : 'Tersembunyi' }}</span></td>
                            <td>
                                <div class="row-actions">
                                    <a href="{{ route('admin.achievements.edit', $achievement) }}" class="btn btn-sm btn-outline"><x-icon name="edit" size="13px" /> Ubah</a>
                                    <form method="POST" action="{{ route('admin.achievements.destroy', $achievement) }}" class="inline-form" onsubmit="return confirm('Hapus data prestasi ini?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger"><x-icon name="trash" size="13px" /></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7"><div class="empty-state">Belum ada data prestasi.</div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @include('partials.pagination', ['paginator' => $achievements])
    </div>
</div>
@endsection
