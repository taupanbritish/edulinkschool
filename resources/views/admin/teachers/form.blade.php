@extends('layouts.admin')

@section('title', $teacher->exists ? 'Ubah Guru' : 'Tambah Guru')

@section('content')
<div class="page-head">
    <div>
        <h1>{{ $teacher->exists ? 'Ubah Data Guru' : 'Tambah Guru' }}</h1>
        <p>Data guru juga dapat ditautkan dengan akun ruang guru.</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('admin.teachers.index') }}" class="btn btn-outline btn-sm"><x-icon name="arrow-left" size="14px" /> Kembali</a>
    </div>
</div>

<form method="POST" enctype="multipart/form-data" action="{{ $teacher->exists ? route('admin.teachers.update', $teacher) : route('admin.teachers.store') }}">
    @csrf
    @if($teacher->exists) @method('PUT') @endif

    <div class="panel">
        <div class="panel-head"><h2>Identitas &amp; Akun</h2></div>
        <div class="panel-body">
            <div class="form-grid">
                <x-photo-field :model="$teacher" label="Foto guru" />

                <div class="field">
                    <label for="name">Nama lengkap</label>
                    <input id="name" name="name" class="input" value="{{ old('name', $teacher->name) }}" required>
                    @error('name')<span class="error">{{ $message }}</span>@enderror
                </div>
                <div class="field">
                    <label for="nip">NIP</label>
                    <input id="nip" name="nip" class="input" value="{{ old('nip', $teacher->nip) }}" required>
                    @error('nip')<span class="error">{{ $message }}</span>@enderror
                </div>
                <div class="field">
                    <label for="gender">Jenis kelamin</label>
                    <select id="gender" name="gender" class="select">
                        <option value="L" @selected(old('gender', $teacher->gender) === 'L')>Laki-laki</option>
                        <option value="P" @selected(old('gender', $teacher->gender) === 'P')>Perempuan</option>
                    </select>
                </div>
                <div class="field">
                    <label for="phone">Nomor telepon</label>
                    <input id="phone" name="phone" class="input" value="{{ old('phone', $teacher->phone) }}">
                </div>
                <div class="field">
                    <label for="email">Email (akun login)</label>
                    <input id="email" type="email" name="email" class="input" value="{{ old('email', $teacher->user?->email) }}" placeholder="guru@sekolah.id">
                    @error('email')<span class="error">{{ $message }}</span>@enderror
                </div>
                <div class="field">
                    <label for="password">Kata sandi</label>
                    <input id="password" type="password" name="password" class="input" placeholder="{{ $teacher->user ? 'Kosongkan jika tidak diubah' : 'Minimal 6 karakter' }}">
                    @error('password')<span class="error">{{ $message }}</span>@enderror
                </div>
            </div>
        </div>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary"><x-icon name="check" size="15px" /> Simpan</button>
        <a href="{{ route('admin.teachers.index') }}" class="btn btn-ghost">Batal</a>
    </div>
</form>
@endsection
