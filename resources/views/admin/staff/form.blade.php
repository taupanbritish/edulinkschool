@extends('layouts.admin')

@section('title', $member->exists ? 'Ubah Staf Tata Usaha' : 'Tambah Staf Tata Usaha')

@section('content')
<div class="page-head">
    <div>
        <h1>{{ $member->exists ? 'Ubah Staf Tata Usaha' : 'Tambah Staf Tata Usaha' }}</h1>
        <p>Lengkapi identitas staf administrasi sekolah.</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('admin.staff.index') }}" class="btn btn-outline btn-sm"><x-icon name="arrow-left" size="14px" /> Kembali</a>
    </div>
</div>

<form method="POST" enctype="multipart/form-data" action="{{ $member->exists ? route('admin.staff.update', $member) : route('admin.staff.store') }}">
    @csrf
    @if($member->exists) @method('PUT') @endif

    <div class="panel">
        <div class="panel-head"><h2>Identitas Staf</h2></div>
        <div class="panel-body">
            <div class="form-grid">
                <x-photo-field :model="$member" label="Foto staf" />

                <div class="field">
                    <label for="name">Nama lengkap</label>
                    <input id="name" name="name" class="input" value="{{ old('name', $member->name) }}" required>
                    @error('name')<span class="error">{{ $message }}</span>@enderror
                </div>
                <div class="field">
                    <label for="nip">NIP</label>
                    <input id="nip" name="nip" class="input" value="{{ old('nip', $member->nip) }}">
                    @error('nip')<span class="error">{{ $message }}</span>@enderror
                </div>
                <div class="field">
                    <label for="position">Posisi / jabatan</label>
                    <input id="position" name="position" class="input" value="{{ old('position', $member->position) }}" required>
                    @error('position')<span class="error">{{ $message }}</span>@enderror
                </div>
                <div class="field">
                    <label for="gender">Jenis kelamin</label>
                    <select id="gender" name="gender" class="select">
                        <option value="L" @selected(old('gender', $member->gender) === 'L')>Laki-laki</option>
                        <option value="P" @selected(old('gender', $member->gender) === 'P')>Perempuan</option>
                    </select>
                </div>
                <div class="field">
                    <label for="phone">Nomor telepon</label>
                    <input id="phone" name="phone" class="input" value="{{ old('phone', $member->phone) }}">
                </div>
                <div class="field">
                    <label for="email">Email</label>
                    <input id="email" type="email" name="email" class="input" value="{{ old('email', $member->email) }}">
                    @error('email')<span class="error">{{ $message }}</span>@enderror
                </div>
                <div class="field">
                    <label for="sort_order">Urutan tampil</label>
                    <input id="sort_order" type="number" name="sort_order" class="input" value="{{ old('sort_order', $member->sort_order ?? 0) }}" min="0" max="999">
                    <span class="hint">Angka kecil tampil lebih dahulu.</span>
                </div>
                <div class="field">
                    <label>Status</label>
                    <input type="hidden" name="is_active" value="0">
                    <label class="checkbox"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $member->is_active ?? true))> Tampilkan sebagai staf aktif</label>
                </div>
            </div>
        </div>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary"><x-icon name="check" size="15px" /> Simpan</button>
        <a href="{{ route('admin.staff.index') }}" class="btn btn-ghost">Batal</a>
    </div>
</form>
@endsection
