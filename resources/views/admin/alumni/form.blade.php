@extends('layouts.admin')

@section('title', $alumni->exists ? 'Ubah Data Alumni' : 'Tambah Data Alumni')

@section('content')
<div class="page-head">
    <div>
        <h1>{{ $alumni->exists ? 'Ubah Data Alumni' : 'Tambah Data Alumni' }}</h1>
        <p>Lengkapi data lulusan dan perguruan tinggi tujuan.</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('admin.alumni.index') }}" class="btn btn-outline btn-sm"><x-icon name="arrow-left" size="14px" /> Kembali</a>
    </div>
</div>

<form method="POST" action="{{ $alumni->exists ? route('admin.alumni.update', $alumni) : route('admin.alumni.store') }}" enctype="multipart/form-data">
    @csrf
    @if($alumni->exists) @method('PUT') @endif

    <div class="panel">
        <div class="panel-head"><h2>Data Lulusan</h2></div>
        <div class="panel-body">
            <div class="form-grid">
                <div class="field">
                    <label for="name">Nama alumni</label>
                    <input id="name" name="name" class="input" value="{{ old('name', $alumni->name) }}" required>
                    @error('name')<span class="error">{{ $message }}</span>@enderror
                </div>
                <div class="field">
                    <label for="graduation_year">Tahun lulus</label>
                    <input id="graduation_year" type="number" name="graduation_year" class="input" value="{{ old('graduation_year', $alumni->graduation_year) }}" min="1980" max="{{ now()->year + 1 }}" required>
                    @error('graduation_year')<span class="error">{{ $message }}</span>@enderror
                </div>
                <div class="field full">
                    <label for="university">Perguruan tinggi</label>
                    <input id="university" name="university" class="input" value="{{ old('university', $alumni->university) }}" required>
                    @error('university')<span class="error">{{ $message }}</span>@enderror
                </div>
                <div class="field">
                    <label for="major">Jurusan / program studi</label>
                    <input id="major" name="major" class="input" value="{{ old('major', $alumni->major) }}">
                </div>
                <div class="field">
                    <label for="type">Jenis perguruan tinggi</label>
                    <select id="type" name="type" class="select">
                        @foreach(\App\Models\AlumniDestination::TYPES as $value => $label)
                            <option value="{{ $value }}" @selected(old('type', $alumni->type) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label for="sort_order">Urutan tampil</label>
                    <input id="sort_order" type="number" name="sort_order" class="input" value="{{ old('sort_order', $alumni->sort_order ?? 0) }}" min="0" max="999">
                </div>
                <div class="field">
                    <input type="hidden" name="is_active" value="0">
                    <label class="checkbox"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $alumni->is_active ?? true))> Tampilkan di website</label>
                </div>
            </div>
        </div>
    </div>

    <div class="panel">
        <div class="panel-head"><h2>Foto Alumni</h2></div>
        <div class="panel-body">
            <div class="form-grid">
                <x-photo-field :model="$alumni" label="Foto alumni" />
            </div>
        </div>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary"><x-icon name="check" size="15px" /> Simpan Data</button>
        <a href="{{ route('admin.alumni.index') }}" class="btn btn-ghost">Batal</a>
    </div>
</form>
@endsection
