@extends('layouts.admin')

@section('title', $user->exists ? 'Ubah Pengguna Panel' : 'Tambah Pengguna Panel')

@section('content')
<div class="page-head">
    <div>
        <h1>{{ $user->exists ? 'Ubah Pengguna Panel' : 'Tambah Pengguna Panel' }}</h1>
        <p>Administrator memiliki akses penuh, petugas web mengelola data dan konten, kepala sekolah hanya dapat melihat data.</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('admin.users.index') }}" class="btn btn-outline btn-sm"><x-icon name="arrow-left" size="14px" /> Kembali</a>
    </div>
</div>

<form method="POST" action="{{ $user->exists ? route('admin.users.update', $user) : route('admin.users.store') }}">
    @csrf
    @if($user->exists) @method('PUT') @endif

    <div class="panel">
        <div class="panel-head"><h2>Identitas &amp; Akun</h2></div>
        <div class="panel-body">
            <div class="form-grid">
                <div class="field">
                    <label for="name">Nama lengkap</label>
                    <input id="name" name="name" class="input" value="{{ old('name', $user->name) }}" required>
                    @error('name')<span class="error">{{ $message }}</span>@enderror
                </div>
                <div class="field">
                    <label for="email">Email (akun login)</label>
                    <input id="email" type="email" name="email" class="input" value="{{ old('email', $user->email) }}" placeholder="petugas@sekolah.id" required>
                    @error('email')<span class="error">{{ $message }}</span>@enderror
                </div>
                <div class="field">
                    <label for="phone">Nomor telepon</label>
                    <input id="phone" name="phone" class="input" value="{{ old('phone', $user->phone) }}">
                    @error('phone')<span class="error">{{ $message }}</span>@enderror
                </div>
                <div class="field">
                    <label for="role">Peran</label>
                    <select id="role" name="role" class="select">
                        @foreach($roleLabels as $value => $label)
                            <option value="{{ $value }}" @selected(old('role', $user->role) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('role')<span class="error">{{ $message }}</span>@enderror
                </div>
                <div class="field">
                    <label for="password">Kata sandi</label>
                    <input id="password" type="password" name="password" class="input" placeholder="{{ $user->exists ? 'Kosongkan jika tidak diubah' : 'Minimal 6 karakter' }}" @required(! $user->exists)>
                    @error('password')<span class="error">{{ $message }}</span>@enderror
                </div>
                <div class="field">
                    <label for="is_active">Status akun</label>
                    <label class="check-line" for="is_active" style="display:flex;gap:.5rem;align-items:center;padding:.65rem 0">
                        <input type="checkbox" id="is_active" name="is_active" value="1" @checked(old('is_active', $user->exists ? $user->is_active : true))>
                        <span>Akun aktif dan dapat masuk ke panel</span>
                    </label>
                    @error('is_active')<span class="error">{{ $message }}</span>@enderror
                </div>
            </div>
        </div>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary"><x-icon name="check" size="15px" /> Simpan</button>
        <a href="{{ route('admin.users.index') }}" class="btn btn-ghost">Batal</a>
    </div>
</form>
@endsection
