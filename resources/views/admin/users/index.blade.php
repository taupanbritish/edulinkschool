@extends('layouts.admin')

@section('title', 'Pengguna Panel')

@section('content')
<div class="page-head">
    <div>
        <h1>Pengguna Panel</h1>
        <p>Kelola akun administrator, petugas web, dan kepala sekolah yang dapat masuk ke panel.</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('admin.users.create') }}" class="btn btn-primary"><x-icon name="plus" size="15px" /> Tambah Pengguna</a>
    </div>
</div>

<div class="panel">
    <div class="panel-head">
        <form method="GET" action="{{ route('admin.users.index') }}" class="filter-bar">
            <input type="search" name="q" value="{{ request('q') }}" class="input" placeholder="Cari nama atau email…">
            <button type="submit" class="btn btn-outline btn-sm"><x-icon name="search" size="14px" /> Cari</button>
        </form>
        <span class="muted" style="font-size:.8rem">{{ $users->total() }} akun</span>
    </div>
    <div class="panel-body flush">
        <div class="table-wrap">
            <table class="data">
                <thead>
                    <tr><th>Nama</th><th>Email</th><th>Peran</th><th>Telepon</th><th>Status</th><th style="text-align:right">Aksi</th></tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr>
                            <td>
                                <span class="cell-main">{{ $user->name }}</span>
                                @if($user->is(auth()->user()))
                                    <span class="cell-sub">Akun Anda</span>
                                @endif
                            </td>
                            <td>{{ $user->email }}</td>
                            <td>
                                <span class="badge {{ $user->isAdmin() ? 'badge-success' : 'badge-neutral' }}">{{ $user->roleLabel() }}</span>
                            </td>
                            <td>{{ $user->phone ?? '-' }}</td>
                            <td>
                                <span class="badge {{ $user->is_active ? 'badge-success' : 'badge-danger' }}">{{ $user->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                            </td>
                            <td>
                                <div class="row-actions">
                                    <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-outline"><x-icon name="edit" size="13px" /> Ubah</a>
                                    @unless($user->is(auth()->user()))
                                        <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="inline-form" onsubmit="return confirm('Hapus akun panel {{ $user->name }}?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" @disabled($user->isAdmin() && $adminCount <= 1) title="{{ $user->isAdmin() && $adminCount <= 1 ? 'Administrator terakhir tidak dapat dihapus' : 'Hapus' }}"><x-icon name="trash" size="13px" /></button>
                                        </form>
                                    @endunless
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6"><div class="empty-state">Belum ada akun panel.</div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @include('partials.pagination', ['paginator' => $users])
    </div>
</div>
@endsection
