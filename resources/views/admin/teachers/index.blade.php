@extends('layouts.admin')

@section('title', 'Data Guru')

@section('content')
<div class="page-head{{ auth()->user()->isWakasekKurikulum() ? ' panel-readonly' : '' }}">
    <div>
        <h1>Data Guru</h1>
        <p>Kelola tenaga pendidik, wali kelas, dan akun ruang guru.</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('admin.imports.form', 'guru') }}" class="btn btn-outline"><x-icon name="upload" size="15px" /> Impor Excel</a>
        @if($withoutAccounts > 0)
            <form method="POST" action="{{ route('admin.accounts.generate', 'guru') }}" class="inline-form" onsubmit="return confirm('Buat akun otomatis untuk {{ $withoutAccounts }} guru tanpa akun? Kata sandi awal: guru123.')">
                @csrf
                <button type="submit" class="btn btn-outline"><x-icon name="refresh" size="15px" /> Generate {{ $withoutAccounts }} Akun</button>
            </form>
        @else
            <button type="button" class="btn btn-outline" disabled title="Semua guru sudah memiliki akun">
                <x-icon name="refresh" size="15px" /> Semua guru sudah berakun
            </button>
        @endif
        <a href="{{ route('admin.teachers.create') }}" class="btn btn-primary"><x-icon name="plus" size="15px" /> Tambah Guru</a>
    </div>
</div>

<div class="panel{{ auth()->user()->isWakasekKurikulum() ? ' panel-readonly' : '' }}">
    <div class="panel-head">
        <form method="GET" action="{{ route('admin.teachers.index') }}" class="filter-bar">
            <input type="search" name="q" value="{{ request('q') }}" class="input" placeholder="Cari nama atau NIP…">
            <button type="submit" class="btn btn-outline btn-sm"><x-icon name="search" size="14px" /> Cari</button>
        </form>
        <span class="muted" style="font-size:.8rem">{{ $teachers->total() }} data</span>
    </div>
    <div class="panel-body flush">
        <div class="table-wrap">
            <table class="data">
                <thead>
                    <tr><th>Nama</th><th>NIP</th><th>Wali Kelas</th><th>Kontak</th><th>Akun</th><th style="text-align:right">Aksi</th></tr>
                </thead>
                <tbody>
                    @forelse($teachers as $teacher)
                        <tr>
                            <td>
                                <div class="cell-photo">
                                    @if($teacher->photoUrl())
                                        <img src="{{ $teacher->photoUrl() }}" alt="" class="thumb">
                                    @else
                                        <span class="thumb thumb-initial">{{ strtoupper(substr($teacher->name, 0, 1)) }}</span>
                                    @endif
                                    <div>
                                        <span class="cell-main">{{ $teacher->name }}</span>
                                        <span class="cell-sub">{{ $teacher->genderLabel() }}</span>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $teacher->nip }}</td>
                            <td>{{ $teacher->classrooms->pluck('name')->join(', ') ?: '-' }}</td>
                            <td>
                                {{ $teacher->phone ?? '-' }}
                                <span class="cell-sub">{{ $teacher->user?->email ?? 'Belum ada akun' }}</span>
                            </td>
                            <td>
                                <span class="badge {{ $teacher->user ? 'badge-success' : 'badge-neutral' }}">{{ $teacher->user ? 'Aktif' : 'Tanpa akun' }}</span>
                            </td>
                            <td>
                                <div class="row-actions">
                                    <a href="{{ route('admin.teachers.edit', $teacher) }}" class="btn btn-sm btn-outline"><x-icon name="edit" size="13px" /> Ubah</a>
                                    <form method="POST" action="{{ route('admin.teachers.destroy', $teacher) }}" class="inline-form" onsubmit="return confirm('Hapus data guru {{ $teacher->name }}?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger"><x-icon name="trash" size="13px" /></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6"><div class="empty-state">Belum ada data guru.</div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @include('partials.pagination', ['paginator' => $teachers])
    </div>
</div>
@endsection
