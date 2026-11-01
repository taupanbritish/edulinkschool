@extends('layouts.admin')

@section('title', $classroom->exists ? 'Ubah Kelas' : 'Tambah Kelas')

@section('content')
<div class="page-head">
    <div>
        <h1>{{ $classroom->exists ? 'Ubah Kelas' : 'Tambah Kelas' }}</h1>
        <p>Nama kelas harus unik dalam satu tahun pelajaran.</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('admin.classrooms.index') }}" class="btn btn-outline btn-sm"><x-icon name="arrow-left" size="14px" /> Kembali</a>
    </div>
</div>

<form method="POST" action="{{ $classroom->exists ? route('admin.classrooms.update', $classroom) : route('admin.classrooms.store') }}">
    @csrf
    @if($classroom->exists) @method('PUT') @endif

    <div class="panel">
        <div class="panel-body">
            <div class="form-grid">
                <div class="field">
                    <label for="name">Nama kelas</label>
                    <input id="name" name="name" class="input" value="{{ old('name', $classroom->name) }}" placeholder="X-1" required>
                    @error('name')<span class="error">{{ $message }}</span>@enderror
                </div>
                <div class="field">
                    <label for="grade_level">Tingkat</label>
                    <select id="grade_level" name="grade_level" class="select">
                        @foreach([10, 11, 12] as $level)
                            <option value="{{ $level }}" @selected(old('grade_level', $classroom->grade_level) == $level)>Kelas {{ $level }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label for="academic_year">Tahun pelajaran</label>
                    <input id="academic_year" name="academic_year" class="input" value="{{ old('academic_year', $classroom->academic_year) }}" placeholder="2026/2027" required>
                    @error('academic_year')<span class="error">{{ $message }}</span>@enderror
                </div>
                <div class="field">
                    <label for="homeroom_teacher_id">Wali kelas</label>
                    <select id="homeroom_teacher_id" name="homeroom_teacher_id" class="select">
                        <option value="">Belum ditentukan</option>
                        @foreach($teachers as $teacher)
                            <option value="{{ $teacher->id }}" @selected(old('homeroom_teacher_id', $classroom->homeroom_teacher_id) == $teacher->id)>{{ $teacher->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary"><x-icon name="check" size="15px" /> Simpan</button>
        <a href="{{ route('admin.classrooms.index') }}" class="btn btn-ghost">Batal</a>
    </div>
</form>
@endsection
