@extends('layouts.admin')

@section('title', 'Wali Murid')

@section('content')
<div class="page-head">
    <div>
        <h1>Wali Murid</h1>
        <p>Data orang tua/wali beserta akun aplikasi mobile.</p>
    </div>
    <div class="page-actions">
        @if($withoutAccounts > 0)
            <form method="POST" action="{{ route('admin.accounts.generate', 'wali') }}" class="inline-form" onsubmit="return confirm('Buat akun otomatis untuk {{ $withoutAccounts }} wali murid tanpa akun? Kata sandi awal: ortu123.')">
                @csrf
                <button type="submit" class="btn btn-outline"><x-icon name="refresh" size="15px" /> Generate {{ $withoutAccounts }} Akun</button>
            </form>
        @else
            <button type="button" class="btn btn-outline" disabled title="Semua wali murid sudah memiliki akun">
                <x-icon name="refresh" size="15px" /> Semua wali sudah berakun
            </button>
        @endif
        <a href="{{ route('admin.guardians.create') }}" class="btn btn-primary"><x-icon name="plus" size="15px" /> Tambah Wali Murid</a>
    </div>
</div>

<div class="panel">
    <div class="panel-head">
        <form method="GET" action="{{ route('admin.guardians.index') }}" class="filter-bar">
            <input type="search" name="q" value="{{ request('q') }}" class="input" placeholder="Cari nama atau telepon…">
            <button type="submit" class="btn btn-outline btn-sm"><x-icon name="search" size="14px" /> Cari</button>
        </form>
        <span class="muted" style="font-size:.8rem">{{ $guardians->total() }} data</span>
    </div>
    <div class="panel-body flush">
        <div class="table-wrap">
            <table class="data">
                <thead>
                    <tr><th>Nama</th><th>Telepon</th><th>Pekerjaan</th><th>Siswa</th><th>Akun</th><th style="text-align:right">Aksi</th></tr>
                </thead>
                <tbody>
                    @forelse($guardians as $guardian)
                        <tr>
                            <td><span class="cell-main">{{ $guardian->name }}</span></td>
                            <td>{{ $guardian->phone ?? '-' }}</td>
                            <td>{{ $guardian->occupation ?? '-' }}</td>
                            <td>{{ $guardian->students_count }} siswa</td>
                            <td><span class="badge {{ $guardian->user ? 'badge-success' : 'badge-neutral' }}">{{ $guardian->user ? 'Aktif' : 'Tanpa akun' }}</span></td>
                            <td>
                                <div class="row-actions">
                                    <a href="{{ route('admin.guardians.edit', $guardian) }}" class="btn btn-sm btn-outline"><x-icon name="edit" size="13px" /> Ubah</a>
                                    <form method="POST" action="{{ route('admin.guardians.destroy', $guardian) }}" class="inline-form" onsubmit="return confirm('Hapus data wali murid {{ $guardian->name }}?@if($guardian->students_count > 0) {{ $guardian->students_count }} siswa akan dilepas dari wali ini.@endif')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger"><x-icon name="trash" size="13px" /></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6"><div class="empty-state">Belum ada data wali murid.</div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @include('partials.pagination', ['paginator' => $guardians])
    </div>
</div>
@endsection
