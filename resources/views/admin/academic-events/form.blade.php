@extends('layouts.admin')

@section('title', $event->exists ? 'Ubah Agenda Akademik' : 'Tambah Agenda Akademik')

@section('content')
<div class="page-head">
    <div>
        <h1>{{ $event->exists ? 'Ubah Agenda Akademik' : 'Tambah Agenda Akademik' }}</h1>
        <p>Agenda akan tampil di halaman Kalender Akademik sesuai urutan tanggal.</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('admin.academic-events.index') }}" class="btn btn-outline btn-sm"><x-icon name="arrow-left" size="14px" /> Kembali</a>
    </div>
</div>

<form method="POST" action="{{ $event->exists ? route('admin.academic-events.update', $event) : route('admin.academic-events.store') }}">
    @csrf
    @if($event->exists) @method('PUT') @endif

    <div class="panel">
        <div class="panel-head"><h2>Detail Agenda</h2></div>
        <div class="panel-body">
            <div class="form-grid">
                <div class="field full">
                    <label for="title">Nama agenda</label>
                    <input id="title" name="title" class="input" value="{{ old('title', $event->title) }}" required>
                    @error('title')<span class="error">{{ $message }}</span>@enderror
                </div>
                <div class="field">
                    <label for="category">Kategori</label>
                    <select id="category" name="category" class="select">
                        @foreach(\App\Models\AcademicEvent::CATEGORIES as $value => $label)
                            <option value="{{ $value }}" @selected(old('category', $event->category) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label for="starts_at">Tanggal mulai</label>
                    <input id="starts_at" type="date" name="starts_at" class="input" value="{{ old('starts_at', $event->starts_at?->format('Y-m-d')) }}" required>
                    @error('starts_at')<span class="error">{{ $message }}</span>@enderror
                </div>
                <div class="field">
                    <label for="ends_at">Tanggal selesai</label>
                    <input id="ends_at" type="date" name="ends_at" class="input" value="{{ old('ends_at', $event->ends_at?->format('Y-m-d')) }}">
                    <span class="hint">Kosongkan jika kegiatan hanya satu hari.</span>
                    @error('ends_at')<span class="error">{{ $message }}</span>@enderror
                </div>
                <div class="field full">
                    <label for="description">Keterangan</label>
                    <textarea id="description" name="description" class="textarea">{{ old('description', $event->description) }}</textarea>
                </div>
                <div class="field">
                    <input type="hidden" name="is_active" value="0">
                    <label class="checkbox"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $event->is_active ?? true))> Tampilkan di website</label>
                </div>
            </div>
        </div>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary"><x-icon name="check" size="15px" /> Simpan Agenda</button>
        <a href="{{ route('admin.academic-events.index') }}" class="btn btn-ghost">Batal</a>
    </div>
</form>
@endsection
