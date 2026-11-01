@extends('layouts.admin')

@section('title', $schedule->exists ? 'Ubah Jadwal' : 'Tambah Jadwal')

@section('content')
<div class="page-head">
    <div>
        <h1>{{ $schedule->exists ? 'Ubah Jadwal' : 'Tambah Jadwal' }}</h1>
        <p>Pilih penugasan mengajar (kelas + mapel + guru), lalu tentukan hari dan jam.</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('admin.schedules.index') }}" class="btn btn-outline btn-sm"><x-icon name="arrow-left" size="14px" /> Kembali</a>
    </div>
</div>

<form method="POST" action="{{ $schedule->exists ? route('admin.schedules.update', $schedule) : route('admin.schedules.store') }}">
    @csrf
    @if($schedule->exists) @method('PUT') @endif

    <div class="panel">
        <div class="panel-body">
            <div class="form-grid">
                <div class="field full">
                    <label for="teaching_assignment_id">Penugasan mengajar</label>
                    <select id="teaching_assignment_id" name="teaching_assignment_id" class="select" required>
                        <option value="">Pilih kelas &amp; mata pelajaran</option>
                        @foreach($assignments as $assignment)
                            <option value="{{ $assignment->id }}" @selected(old('teaching_assignment_id', $schedule->teaching_assignment_id) == $assignment->id)>
                                {{ $assignment->classroom->name }} · {{ $assignment->subject->name }} · {{ $assignment->teacher->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('teaching_assignment_id')<span class="error">{{ $message }}</span>@enderror
                </div>
                <div class="field">
                    <label for="day">Hari</label>
                    <select id="day" name="day" class="select">
                        @foreach(\App\Models\Schedule::DAYS as $value => $label)
                            <option value="{{ $value }}" @selected(old('day', $schedule->day) == $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label for="room">Ruang</label>
                    <input id="room" name="room" class="input" value="{{ old('room', $schedule->room) }}" placeholder="X-1 / Lab IPA">
                </div>
                <div class="field">
                    <label for="starts_at">Jam mulai</label>
                    <input id="starts_at" type="time" name="starts_at" class="input" value="{{ old('starts_at', $schedule->starts_at ? substr($schedule->starts_at, 0, 5) : '') }}" required>
                    @error('starts_at')<span class="error">{{ $message }}</span>@enderror
                </div>
                <div class="field">
                    <label for="ends_at">Jam selesai</label>
                    <input id="ends_at" type="time" name="ends_at" class="input" value="{{ old('ends_at', $schedule->ends_at ? substr($schedule->ends_at, 0, 5) : '') }}" required>
                    @error('ends_at')<span class="error">{{ $message }}</span>@enderror
                </div>
            </div>
        </div>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary"><x-icon name="check" size="15px" /> Simpan</button>
        <a href="{{ route('admin.schedules.index') }}" class="btn btn-ghost">Batal</a>
    </div>
</form>
@endsection
