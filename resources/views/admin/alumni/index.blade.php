@extends('layouts.admin')

@section('title', 'Info PTN / Alumni')

@section('content')
<div class="page-head">
    <div>
        <h1>Info PTN / Alumni</h1>
        <p>Data sebaran lulusan yang diterima di PTN atau universitas ternama.</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('alumni') }}" target="_blank" rel="noopener" class="btn btn-outline btn-sm"><x-icon name="arrow-right" size="14px" /> Lihat halaman</a>
        <a href="{{ route('admin.alumni.create') }}" class="btn btn-primary"><x-icon name="plus" size="15px" /> Tambah Alumni</a>
    </div>
</div>

<div class="panel">
    <div class="panel-head">
        <form method="GET" action="{{ route('admin.alumni.index') }}" class="filter-bar">
            <input type="search" name="q" value="{{ request('q') }}" class="input" placeholder="Cari nama, universitas, atau jurusan…">
            <select name="type" class="select">
                <option value="">Semua jenis</option>
                @foreach(\App\Models\AlumniDestination::TYPES as $value => $label)
                    <option value="{{ $value }}" @selected(request('type') === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-outline btn-sm"><x-icon name="search" size="14px" /> Cari</button>
            @if(request('q') || request('type'))
                <a href="{{ route('admin.alumni.index') }}" class="btn btn-ghost btn-sm">Reset</a>
            @endif
        </form>
        <span class="muted" style="font-size:.8rem">{{ $alumni->total() }} data</span>
    </div>
    <div class="panel-body flush">
        <div class="table-wrap">
            <table class="data">
                <thead>
                    <tr><th>Foto</th><th>Nama</th><th>Universitas</th><th>Jurusan</th><th>Tahun lulus</th><th>Jenis</th><th>Status</th><th style="text-align:right">Aksi</th></tr>
                </thead>
                <tbody>
                    @forelse($alumni as $item)
                        <tr>
                            <td>
                                @if($item->photoUrl())
                                    <img src="{{ $item->photoUrl() }}" alt="Foto {{ $item->name }}" class="alumni-thumb" loading="lazy">
                                @else
                                    <span class="alumni-thumb alumni-thumb-empty"><x-icon name="user" size="16px" /></span>
                                @endif
                            </td>
                            <td><span class="cell-main">{{ $item->name }}</span></td>
                            <td>{{ $item->university }}</td>
                            <td>{{ $item->major ?? '-' }}</td>
                            <td>{{ $item->graduation_year }}</td>
                            <td><span class="badge {{ $item->type === 'ptn' ? 'badge-success' : 'badge-info' }}">{{ $item->typeLabel() }}</span></td>
                            <td><span class="badge {{ $item->is_active ? 'badge-success' : 'badge-neutral' }}">{{ $item->is_active ? 'Tampil' : 'Tersembunyi' }}</span></td>
                            <td>
                                <div class="row-actions">
                                    <a href="{{ route('admin.alumni.edit', $item) }}" class="btn btn-sm btn-outline"><x-icon name="edit" size="13px" /> Ubah</a>
                                    <form method="POST" action="{{ route('admin.alumni.destroy', $item) }}" class="inline-form" onsubmit="return confirm('Hapus data alumni ini?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger"><x-icon name="trash" size="13px" /></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8"><div class="empty-state">Belum ada data alumni.</div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @include('partials.pagination', ['paginator' => $alumni])
    </div>
</div>
@endsection
