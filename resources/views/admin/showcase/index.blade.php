@extends('layouts.admin')

@section('title', $title)

@section('content')
<div class="page-head">
    <div>
        <h1>{{ $title }}</h1>
        <p>{{ $subtitle }}</p>
    </div>
    <div class="page-actions">
        <a href="{{ route($publicRoute) }}" target="_blank" rel="noopener" class="btn btn-outline btn-sm"><x-icon name="arrow-right" size="14px" /> Lihat halaman</a>
        <a href="{{ route($routePrefix.'.create') }}" class="btn btn-primary"><x-icon name="plus" size="15px" /> Tambah {{ $entityLabel }}</a>
    </div>
</div>

<div class="panel">
    <div class="panel-head">
        <form method="GET" action="{{ route($routePrefix.'.index') }}" class="filter-bar">
            <input type="search" name="q" value="{{ request('q') }}" class="input" placeholder="Cari nama…">
            <button type="submit" class="btn btn-outline btn-sm"><x-icon name="search" size="14px" /> Cari</button>
            @if(request('q'))
                <a href="{{ route($routePrefix.'.index') }}" class="btn btn-ghost btn-sm">Reset</a>
            @endif
        </form>
        <span class="muted" style="font-size:.8rem">{{ $items->total() }} data</span>
    </div>
    <div class="panel-body flush">
        <div class="table-wrap">
            <table class="data">
                <thead>
                    <tr><th>Nama</th><th>Media</th><th>Urutan</th><th>Status</th><th style="text-align:right">Aksi</th></tr>
                </thead>
                <tbody>
                    @forelse($items as $item)
                        <tr>
                            <td>
                                <div class="cell-photo">
                                    @if($item->photoUrl())
                                        <img src="{{ $item->photoUrl() }}" alt="" class="thumb">
                                    @else
                                        <span class="thumb thumb-initial">{{ strtoupper(substr($item->name, 0, 1)) }}</span>
                                    @endif
                                    <div>
                                        <span class="cell-main">{{ $item->name }}</span>
                                        <span class="cell-sub">{{ $item->description ? \Illuminate\Support\Str::limit($item->description, 90) : 'Tanpa keterangan' }}</span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge {{ $item->photoUrl() ? 'badge-success' : 'badge-neutral' }}">{{ $item->photoUrl() ? 'Gambar' : 'Tanpa gambar' }}</span>
                                @if($item->videoEmbedUrl())
                                    <span class="badge badge-info">YouTube</span>
                                @elseif($item->videoFileUrl())
                                    <span class="badge badge-info">Video</span>
                                @else
                                    <span class="badge badge-neutral">Tanpa video</span>
                                @endif
                            </td>
                            <td>{{ $item->sort_order }}</td>
                            <td><span class="badge {{ $item->is_active ? 'badge-success' : 'badge-neutral' }}">{{ $item->is_active ? 'Tampil' : 'Tersembunyi' }}</span></td>
                            <td>
                                <div class="row-actions">
                                    <a href="{{ route($routePrefix.'.edit', $item) }}" class="btn btn-sm btn-outline"><x-icon name="edit" size="13px" /> Ubah</a>
                                    <form method="POST" action="{{ route($routePrefix.'.destroy', $item) }}" class="inline-form" onsubmit="return confirm('Hapus data ini? Gambar dan video terkait juga akan dihapus.')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger"><x-icon name="trash" size="13px" /></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5"><div class="empty-state">{{ $emptyMessage }}</div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @include('partials.pagination', ['paginator' => $items])
    </div>
</div>
@endsection
