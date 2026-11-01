@extends('layouts.admin')

@section('title', 'Kurikulum')

@section('content')
<div class="page-head">
    <div>
        <h1>Kurikulum</h1>
        <p>Kelola kelompok mata pelajaran dan peminatan yang tampil di halaman Kurikulum.</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('curriculum') }}" target="_blank" rel="noopener" class="btn btn-outline btn-sm"><x-icon name="arrow-right" size="14px" /> Lihat halaman</a>
        <a href="{{ route('admin.curriculum-groups.create') }}" class="btn btn-primary"><x-icon name="plus" size="15px" /> Tambah Kelompok</a>
    </div>
</div>

<div class="panel">
    <div class="panel-head">
        <form method="GET" action="{{ route('admin.curriculum-groups.index') }}" class="filter-bar">
            <input type="search" name="q" value="{{ request('q') }}" class="input" placeholder="Cari nama kelompok…">
            <button type="submit" class="btn btn-outline btn-sm"><x-icon name="search" size="14px" /> Cari</button>
            @if(request('q'))
                <a href="{{ route('admin.curriculum-groups.index') }}" class="btn btn-ghost btn-sm">Reset</a>
            @endif
        </form>
        <span class="muted" style="font-size:.8rem">{{ $groups->total() }} kelompok</span>
    </div>
    <div class="panel-body flush">
        <div class="table-wrap">
            <table class="data">
                <thead>
                    <tr><th>Kelompok</th><th>Jumlah mapel</th><th>Urutan</th><th>Status</th><th style="text-align:right">Aksi</th></tr>
                </thead>
                <tbody>
                    @forelse($groups as $group)
                        <tr>
                            <td>
                                <span class="cell-main">{{ $group->name }}</span>
                                <span class="cell-sub">{{ $group->description ? \Illuminate\Support\Str::limit($group->description, 90) : 'Tanpa keterangan' }}</span>
                            </td>
                            <td>{{ count($group->subjectList()) }} mata pelajaran</td>
                            <td>{{ $group->sort_order }}</td>
                            <td><span class="badge {{ $group->is_active ? 'badge-success' : 'badge-neutral' }}">{{ $group->is_active ? 'Tampil' : 'Tersembunyi' }}</span></td>
                            <td>
                                <div class="row-actions">
                                    <a href="{{ route('admin.curriculum-groups.edit', $group) }}" class="btn btn-sm btn-outline"><x-icon name="edit" size="13px" /> Ubah</a>
                                    <form method="POST" action="{{ route('admin.curriculum-groups.destroy', $group) }}" class="inline-form" onsubmit="return confirm('Hapus kelompok kurikulum ini?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger"><x-icon name="trash" size="13px" /></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5"><div class="empty-state">Belum ada kelompok kurikulum.</div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @include('partials.pagination', ['paginator' => $groups])
    </div>
</div>
@endsection
