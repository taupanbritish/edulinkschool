@extends('layouts.admin')

@section('title', $guardian->exists ? 'Ubah Wali Murid' : 'Tambah Wali Murid')

@section('content')
<div class="page-head">
    <div>
        <h1>{{ $guardian->exists ? 'Ubah Wali Murid' : 'Tambah Wali Murid' }}</h1>
        <p>Akun login diperlukan agar orang tua dapat memakai aplikasi mobile.</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('admin.guardians.index') }}" class="btn btn-outline btn-sm"><x-icon name="arrow-left" size="14px" /> Kembali</a>
    </div>
</div>

<form method="POST" action="{{ $guardian->exists ? route('admin.guardians.update', $guardian) : route('admin.guardians.store') }}">
    @csrf
    @if($guardian->exists) @method('PUT') @endif

    <div class="panel">
        <div class="panel-head"><h2>Identitas &amp; Akun</h2></div>
        <div class="panel-body">
            <div class="form-grid">
                <div class="field">
                    <label for="name">Nama lengkap</label>
                    <input id="name" name="name" class="input" value="{{ old('name', $guardian->name) }}" required>
                    @error('name')<span class="error">{{ $message }}</span>@enderror
                </div>
                <div class="field">
                    <label for="phone">Nomor telepon</label>
                    <input id="phone" name="phone" class="input" value="{{ old('phone', $guardian->phone) }}">
                </div>
                <div class="field">
                    <label for="occupation">Pekerjaan</label>
                    <input id="occupation" name="occupation" class="input" value="{{ old('occupation', $guardian->occupation) }}">
                </div>
                <div class="field">
                    <label for="email">Email (akun login)</label>
                    <input id="email" type="email" name="email" class="input" value="{{ old('email', $guardian->user?->email) }}" placeholder="ortu@sekolah.id">
                    @error('email')<span class="error">{{ $message }}</span>@enderror
                </div>
                <div class="field">
                    <label for="password">Kata sandi</label>
                    <input id="password" type="password" name="password" class="input" placeholder="{{ $guardian->user ? 'Kosongkan jika tidak diubah' : 'Minimal 6 karakter' }}">
                    @error('password')<span class="error">{{ $message }}</span>@enderror
                </div>
                <div class="field full">
                    <label for="address">Alamat</label>
                    <textarea id="address" name="address" class="textarea">{{ old('address', $guardian->address) }}</textarea>
                </div>
            </div>
        </div>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary"><x-icon name="check" size="15px" /> Simpan</button>
        <a href="{{ route('admin.guardians.index') }}" class="btn btn-ghost">Batal</a>
    </div>
</form>
@endsection
