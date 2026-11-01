@extends('layouts.admin')

@section('title', 'Organisasi Siswa')

@section('content')
<div class="page-head">
    <div>
        <h1>Organisasi Siswa</h1>
        <p>Kelola informasi OSIS dan MPK yang tampil di halaman Kesiswaan.</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('student-organizations') }}" target="_blank" rel="noopener" class="btn btn-outline btn-sm"><x-icon name="arrow-right" size="14px" /> Lihat halaman</a>
        <a href="{{ route('admin.student-organizations.create') }}" class="btn btn-primary"><x-icon name="plus" size="15px" /> Tambah Organisasi</a>
    </div>
</div>

<div class="panel">
    <div class="panel-head">
        <form method="GET" action="{{ route('admin.student-organizations.index') }}" class="filter-bar">
            <input type="search" name="q" value="{{ request('q') }}" class="input" placeholder="Cari nama atau pembina…">
            <button type="submit" class="btn btn-outline btn-sm"><x-icon name="search" size="14px" /> Cari</button>
            @if(request('q'))
                <a href="{{ route('admin.student-organizations.index') }}" class="btn btn-ghost btn-sm">Reset</a>
            @endif
        </form>
        <span class="muted" style="font-size:.8rem">{{ $organizations->total() }} organisasi</span>
    </div>
    <div class="panel-body flush">
        <div class="table-wrap">
            <table class="data">
                <thead>
                    <tr><th>Organisasi</th><th>Pembina</th><th>Program</th><th>Status</th><th style="text-align:right">Aksi</th></tr>
                </thead>
                <tbody>
                    @forelse($organizations as $organization)
                        <tr>
                            <td>
                                <div class="cell-photo">
                                    @if($organization->photoUrl())
                                        <img src="{{ $organization->photoUrl() }}" alt="" class="thumb">
                                    @else
                                        <span class="thumb thumb-initial">{{ strtoupper(substr($organization->short_name ?? $organization->name, 0, 2)) }}</span>
                                    @endif
                                    <div>
                                        <span class="cell-main">{{ $organization->short_name ? $organization->short_name.' — ' : '' }}{{ $organization->name }}</span>
                                        <span class="cell-sub">{{ $organization->description ? \Illuminate\Support\Str::limit($organization->description, 85) : 'Tanpa keterangan' }}</span>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $organization->advisor ?? '-' }}</td>
                            <td>{{ count($organization->programList()) }} program</td>
                            <td><span class="badge {{ $organization->is_active ? 'badge-success' : 'badge-neutral' }}">{{ $organization->is_active ? 'Tampil' : 'Tersembunyi' }}</span></td>
                            <td>
                                <div class="row-actions">
                                    <a href="{{ route('admin.student-organizations.edit', $organization) }}" class="btn btn-sm btn-outline"><x-icon name="edit" size="13px" /> Ubah</a>
                                    <form method="POST" action="{{ route('admin.student-organizations.destroy', $organization) }}" class="inline-form" onsubmit="return confirm('Hapus organisasi ini? Foto terkait juga akan dihapus.')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger"><x-icon name="trash" size="13px" /></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5"><div class="empty-state">Belum ada organisasi siswa.</div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @include('partials.pagination', ['paginator' => $organizations])
    </div>
</div>
@endsection
