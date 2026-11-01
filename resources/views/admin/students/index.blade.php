@extends('layouts.admin')

@section('title', 'Data Siswa')

@section('content')
<div class="page-head">
    <div>
        <h1>Data Siswa</h1>
        <p>Kelola identitas siswa, kelas, wali murid, dan akun akses aplikasi mobile.</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('admin.imports.form', 'siswa') }}" class="btn btn-outline"><x-icon name="upload" size="15px" /> Impor Excel</a>
        @if($withoutAccounts > 0)
            <form method="POST" action="{{ route('admin.accounts.generate', 'siswa') }}" class="inline-form" onsubmit="return confirm('Buat akun otomatis untuk {{ $withoutAccounts }} siswa tanpa akun? Kata sandi awal: siswa123.')">
                @csrf
                <button type="submit" class="btn btn-outline"><x-icon name="refresh" size="15px" /> Generate {{ $withoutAccounts }} Akun</button>
            </form>
        @else
            <button type="button" class="btn btn-outline" disabled title="Semua siswa sudah memiliki akun">
                <x-icon name="refresh" size="15px" /> Semua siswa sudah berakun
            </button>
        @endif
        <a href="{{ route('admin.students.create') }}" class="btn btn-primary"><x-icon name="plus" size="15px" /> Tambah Siswa</a>
    </div>
</div>

<div class="panel">
    <div class="panel-head">
        <form method="GET" action="{{ route('admin.students.index') }}" class="filter-bar">
            <input type="search" name="q" value="{{ request('q') }}" class="input" placeholder="Cari nama, NIS, atau NISN…">
            <select name="classroom" class="select">
                <option value="">Semua kelas</option>
                @foreach($classrooms as $classroom)
                    <option value="{{ $classroom->id }}" @selected(request('classroom') == $classroom->id)>{{ $classroom->label() }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-outline btn-sm"><x-icon name="search" size="14px" /> Cari</button>
            @if(request()->hasAny(['q', 'classroom']))
                <a href="{{ route('admin.students.index') }}" class="btn btn-ghost btn-sm">Reset</a>
            @endif
        </form>
        <span class="muted" style="font-size:.8rem">{{ $students->total() }} data</span>
    </div>
    <div class="panel-body flush">
        <div class="table-wrap">
            <table class="data">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>NIS / NISN</th>
                        <th>Kelas</th>
                        <th>Wali Murid</th>
                        <th>Status</th>
                        <th style="text-align:right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($students as $student)
                        <tr>
                            <td>
                                <div class="cell-photo">
                                    @if($student->photoUrl())
                                        <img src="{{ $student->photoUrl() }}" alt="" class="thumb">
                                    @else
                                        <span class="thumb thumb-initial">{{ strtoupper(substr($student->name, 0, 1)) }}</span>
                                    @endif
                                    <div>
                                        <span class="cell-main">{{ $student->name }}</span>
                                        <span class="cell-sub">{{ $student->genderLabel() }} · {{ $student->user ? 'Punya akun' : 'Belum ada akun' }}</span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                {{ $student->nis }}
                                <span class="cell-sub">{{ $student->nisn ?? '-' }}</span>
                            </td>
                            <td>{{ $student->classroom->name }}</td>
                            <td>{{ $student->guardian?->name ?? '-' }}</td>
                            <td>
                                <span class="badge {{ $student->status === 'aktif' ? 'badge-success' : 'badge-neutral' }}">{{ ucfirst($student->status) }}</span>
                            </td>
                            <td>
                                <div class="row-actions">
                                    <a href="{{ route('admin.students.edit', $student) }}" class="btn btn-sm btn-outline"><x-icon name="edit" size="13px" /> Ubah</a>
                                    <form method="POST" action="{{ route('admin.students.destroy', $student) }}" class="inline-form" onsubmit="return confirm('Hapus data siswa {{ $student->name }}? Nilai dan absensi terkait juga akan terhapus.')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger"><x-icon name="trash" size="13px" /></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6"><div class="empty-state">Belum ada siswa yang cocok dengan filter.</div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @include('partials.pagination', ['paginator' => $students])
    </div>
</div>
@endsection
