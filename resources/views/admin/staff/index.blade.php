@extends('layouts.admin')

@section('title', 'Tata Usaha')

@section('content')
<div class="page-head">
    <div>
        <h1>Tata Usaha</h1>
        <p>Kelola staf administrasi sekolah beserta foto dan kontaknya.</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('admin.staff.create') }}" class="btn btn-primary"><x-icon name="plus" size="15px" /> Tambah Staf</a>
    </div>
</div>

<div class="panel">
    <div class="panel-head">
        <form method="GET" action="{{ route('admin.staff.index') }}" class="filter-bar">
            <input type="search" name="q" value="{{ request('q') }}" class="input" placeholder="Cari nama, NIP, atau posisi…">
            <button type="submit" class="btn btn-outline btn-sm"><x-icon name="search" size="14px" /> Cari</button>
            @if(request('q'))
                <a href="{{ route('admin.staff.index') }}" class="btn btn-ghost btn-sm">Reset</a>
            @endif
        </form>
        <span class="muted" style="font-size:.8rem">{{ $staff->total() }} data</span>
    </div>
    <div class="panel-body flush">
        <div class="table-wrap">
            <table class="data">
                <thead>
                    <tr><th>Nama</th><th>NIP</th><th>Posisi</th><th>Kontak</th><th>Status</th><th style="text-align:right">Aksi</th></tr>
                </thead>
                <tbody>
                    @forelse($staff as $member)
                        <tr>
                            <td>
                                <div class="cell-photo">
                                    @if($member->photoUrl())
                                        <img src="{{ $member->photoUrl() }}" alt="" class="thumb">
                                    @else
                                        <span class="thumb thumb-initial">{{ strtoupper(substr($member->name, 0, 1)) }}</span>
                                    @endif
                                    <div>
                                        <span class="cell-main">{{ $member->name }}</span>
                                        <span class="cell-sub">{{ $member->genderLabel() }}</span>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $member->nip ?? '-' }}</td>
                            <td>{{ $member->position }}</td>
                            <td>
                                {{ $member->phone ?? '-' }}
                                <span class="cell-sub">{{ $member->email ?? 'Tanpa email' }}</span>
                            </td>
                            <td>
                                <span class="badge {{ $member->is_active ? 'badge-success' : 'badge-neutral' }}">{{ $member->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                            </td>
                            <td>
                                <div class="row-actions">
                                    <a href="{{ route('admin.staff.edit', $member) }}" class="btn btn-sm btn-outline"><x-icon name="edit" size="13px" /> Ubah</a>
                                    <form method="POST" action="{{ route('admin.staff.destroy', $member) }}" class="inline-form" onsubmit="return confirm('Hapus data staf {{ $member->name }}?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger"><x-icon name="trash" size="13px" /></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6"><div class="empty-state">Belum ada data staf tata usaha.</div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @include('partials.pagination', ['paginator' => $staff])
    </div>
</div>
@endsection
