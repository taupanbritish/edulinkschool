@extends('layouts.admin')

@section('title', 'Kelas')

@section('content')
<div class="page-head{{ auth()->user()->isWakasekKurikulum() ? ' panel-readonly' : '' }}">
    <div>
        <h1>Kelas</h1>
        <p>Rombongan belajar, wali kelas, dan tahun pelajaran.</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('admin.imports.form', 'kelas') }}" class="btn btn-outline"><x-icon name="upload" size="15px" /> Impor Excel</a>
        <a href="{{ route('admin.classrooms.create') }}" class="btn btn-primary"><x-icon name="plus" size="15px" /> Tambah Kelas</a>
    </div>
</div>

<div class="panel{{ auth()->user()->isWakasekKurikulum() ? ' panel-readonly' : '' }}">
    <div class="panel-body flush">
        <div class="table-wrap">
            <table class="data">
                <thead>
                    <tr><th>Kelas</th><th>Tingkat</th><th>Tahun Pelajaran</th><th>Wali Kelas</th><th>Siswa</th><th>Mapel</th><th style="text-align:right">Aksi</th></tr>
                </thead>
                <tbody>
                    @forelse($classrooms as $classroom)
                        <tr>
                            <td><span class="cell-main">{{ $classroom->name }}</span></td>
                            <td>Kelas {{ $classroom->grade_level }}</td>
                            <td>{{ $classroom->academic_year }}</td>
                            <td>{{ $classroom->homeroomTeacher?->name ?? '-' }}</td>
                            <td>{{ $classroom->students_count }}</td>
                            <td>{{ $classroom->assignments_count }}</td>
                            <td>
                                <div class="row-actions">
                                    <a href="{{ route('admin.classrooms.edit', $classroom) }}" class="btn btn-sm btn-outline"><x-icon name="edit" size="13px" /> Ubah</a>
                                    <form method="POST" action="{{ route('admin.classrooms.destroy', $classroom) }}" class="inline-form" onsubmit="return confirm('Hapus kelas {{ $classroom->name }}?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger"><x-icon name="trash" size="13px" /></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7"><div class="empty-state">Belum ada kelas.</div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @include('partials.pagination', ['paginator' => $classrooms])
    </div>
</div>
@endsection
