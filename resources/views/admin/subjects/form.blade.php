@extends('layouts.admin')

@section('title', $subject->exists ? 'Ubah Mapel' : 'Tambah Mapel')

@section('content')
<div class="page-head">
    <div>
        <h1>{{ $subject->exists ? 'Ubah Mata Pelajaran' : 'Tambah Mata Pelajaran' }}</h1>
        <p>Kode mata pelajaran harus unik.</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('admin.subjects.index') }}" class="btn btn-outline btn-sm"><x-icon name="arrow-left" size="14px" /> Kembali</a>
    </div>
</div>

<form method="POST" action="{{ $subject->exists ? route('admin.subjects.update', $subject) : route('admin.subjects.store') }}">
    @csrf
    @if($subject->exists) @method('PUT') @endif

    <div class="panel">
        <div class="panel-body">
            <div class="form-grid">
                <div class="field">
                    <label for="code">Kode</label>
                    <input id="code" name="code" class="input" value="{{ old('code', $subject->code) }}" placeholder="MTK" required>
                    @error('code')<span class="error">{{ $message }}</span>@enderror
                </div>
                <div class="field">
                    <label for="name">Nama mata pelajaran</label>
                    <input id="name" name="name" class="input" value="{{ old('name', $subject->name) }}" required>
                    @error('name')<span class="error">{{ $message }}</span>@enderror
                </div>
                <div class="field full">
                    <label for="description">Deskripsi</label>
                    <textarea id="description" name="description" class="textarea">{{ old('description', $subject->description) }}</textarea>
                </div>
            </div>
        </div>
    </div>

    <div class="panel mt-2">
        <div class="panel-head">
            <h2>Penugasan Jadwal</h2>
            <span class="muted" style="font-size:.78rem">Opsional — isi untuk langsung menugaskan mapel ini.</span>
        </div>
        <div class="panel-body">
            <p class="muted" style="font-size:.82rem;margin-top:0">
                Pilih kelas dan guru, lalu tentukan hari serta jam pertemuan. Penugasan dan jadwal akan dibuat bersamaan.
                Jadwal berikutnya dapat ditambahkan melalui menu
                <a href="{{ route('admin.schedules.index') }}">Jadwal</a>.
            </p>

            <div class="form-grid">
                <div class="field">
                    <label for="classroom_id">Kelas</label>
                    <select id="classroom_id" name="classroom_id" class="select">
                        <option value="">Pilih kelas</option>
                        @foreach($classrooms as $classroom)
                            <option value="{{ $classroom->id }}" @selected(old('classroom_id') == $classroom->id)>{{ $classroom->label() }}</option>
                        @endforeach
                    </select>
                    @error('classroom_id')<span class="error">{{ $message }}</span>@enderror
                </div>
                <div class="field">
                    <label for="teacher_id">Guru pengampu</label>
                    <select id="teacher_id" name="teacher_id" class="select">
                        <option value="">Pilih guru</option>
                        @foreach($teachers as $teacher)
                            <option value="{{ $teacher->id }}" @selected(old('teacher_id') == $teacher->id)>{{ $teacher->name }}</option>
                        @endforeach
                    </select>
                    @error('teacher_id')<span class="error">{{ $message }}</span>@enderror
                </div>
                <div class="field">
                    <label for="day">Hari</label>
                    <select id="day" name="day" class="select">
                        <option value="">Pilih hari</option>
                        @foreach(\App\Models\Schedule::DAYS as $value => $label)
                            <option value="{{ $value }}" @selected(old('day') == $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('day')<span class="error">{{ $message }}</span>@enderror
                </div>
                <div class="field">
                    <label for="room">Ruang</label>
                    <input id="room" name="room" class="input" value="{{ old('room') }}" placeholder="X-1 / Lab IPA">
                    @error('room')<span class="error">{{ $message }}</span>@enderror
                </div>
                <div class="field">
                    <label for="starts_at">Jam mulai</label>
                    <input id="starts_at" type="time" name="starts_at" class="input" value="{{ old('starts_at') }}">
                    @error('starts_at')<span class="error">{{ $message }}</span>@enderror
                </div>
                <div class="field">
                    <label for="ends_at">Jam selesai</label>
                    <input id="ends_at" type="time" name="ends_at" class="input" value="{{ old('ends_at') }}">
                    @error('ends_at')<span class="error">{{ $message }}</span>@enderror
                </div>
            </div>

            @if($subject->exists && $subject->assignments->isNotEmpty())
                <div class="table-wrap" style="margin-top:1.1rem">
                    <table class="data">
                        <thead>
                            <tr><th>Kelas</th><th>Guru</th><th>Jadwal</th><th style="text-align:right">Aksi</th></tr>
                        </thead>
                        <tbody>
                            @foreach($subject->assignments as $assignment)
                                <tr>
                                    <td>{{ $assignment->classroom->name }}</td>
                                    <td>{{ $assignment->teacher->name }}</td>
                                    <td>
                                        <div class="schedule-list">
                                            @forelse($assignment->schedules as $schedule)
                                                <div class="schedule-item">
                                                    <span class="badge badge-neutral">{{ $schedule->dayName() }} {{ substr($schedule->starts_at, 0, 5) }}–{{ substr($schedule->ends_at, 0, 5) }}</span>
                                                    <a href="{{ route('admin.schedules.edit', $schedule) }}" class="btn btn-sm btn-ghost" title="Ubah jadwal"><x-icon name="edit" size="12px" /></a>
                                                    <button type="submit" form="delete-schedule-{{ $schedule->id }}" class="btn btn-sm btn-ghost" title="Hapus jadwal" onclick="return confirm({{ \Illuminate\Support\Js::from('Hapus jadwal '.$schedule->dayName().' '.substr($schedule->starts_at, 0, 5).'?') }})"><x-icon name="trash" size="12px" /></button>
                                                </div>
                                            @empty
                                                <span class="muted">Belum ada jam</span>
                                            @endforelse
                                        </div>
                                    </td>
                                    <td>
                                        <div class="row-actions">
                                            <a href="{{ route('admin.schedules.create', ['assignment' => $assignment->id]) }}" class="btn btn-sm btn-outline"><x-icon name="plus" size="12px" /> Jam</a>
                                            <button type="submit" form="delete-assignment-{{ $assignment->id }}" class="btn btn-sm btn-danger" title="Hapus penugasan" onclick="return confirm({{ \Illuminate\Support\Js::from('Hapus penugasan '.$assignment->classroom->name.' · '.$assignment->teacher->name.'? Jadwal dan nilai terkait juga akan dihapus.') }})"><x-icon name="trash" size="12px" /></button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary"><x-icon name="check" size="15px" /> Simpan</button>
        <a href="{{ route('admin.subjects.index') }}" class="btn btn-ghost">Batal</a>
    </div>
</form>

@if($subject->exists)
    @foreach($subject->assignments as $assignment)
        @foreach($assignment->schedules as $schedule)
            <form id="delete-schedule-{{ $schedule->id }}" method="POST" action="{{ route('admin.schedules.destroy', $schedule) }}" hidden>
                @csrf @method('DELETE')
            </form>
        @endforeach
        <form id="delete-assignment-{{ $assignment->id }}" method="POST" action="{{ route('admin.subjects.assignments.destroy', [$subject, $assignment]) }}" hidden>
            @csrf @method('DELETE')
        </form>
    @endforeach
@endif
@endsection
