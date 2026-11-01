@extends('layouts.admin')

@section('title', $member->exists ? 'Ubah Anggota Organisasi' : 'Tambah Anggota Organisasi')

@section('content')
<div class="page-head">
    <div>
        <h1>{{ $member->exists ? 'Ubah Anggota Organisasi' : 'Tambah Anggota Organisasi' }}</h1>
        <p>Pilih atasan langsung untuk membentuk bagan organisasi.</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('admin.organization.index') }}" class="btn btn-outline btn-sm"><x-icon name="arrow-left" size="14px" /> Kembali</a>
    </div>
</div>

<form method="POST" enctype="multipart/form-data" action="{{ $member->exists ? route('admin.organization.update', $member) : route('admin.organization.store') }}">
    @csrf
    @if($member->exists) @method('PUT') @endif

    <div class="panel">
        <div class="panel-head"><h2>Identitas Jabatan</h2></div>
        <div class="panel-body">
            <div class="form-grid">
                <x-photo-field :model="$member" label="Foto anggota" />

                <div class="field">
                    <label for="name">Nama lengkap</label>
                    <input id="name" name="name" class="input" value="{{ old('name', $member->name) }}" required>
                    @error('name')<span class="error">{{ $message }}</span>@enderror
                </div>
                <div class="field">
                    <label for="position">Jabatan</label>
                    <input id="position" name="position" class="input" value="{{ old('position', $member->position) }}" placeholder="Contoh: Wakil Kepala Sekolah" required>
                    @error('position')<span class="error">{{ $message }}</span>@enderror
                </div>
                <div class="field">
                    <label for="parent_id">Atasan langsung</label>
                    <select id="parent_id" name="parent_id" class="select">
                        <option value="">Tidak ada (puncak bagan)</option>
                        @foreach($parentOptions as $row)
                            <option value="{{ $row['member']->id }}" @selected(old('parent_id', $member->parent_id) == $row['member']->id)>{{ str_repeat('— ', $row['depth']) }}{{ $row['member']->name }} — {{ $row['member']->position }}</option>
                        @endforeach
                    </select>
                    @error('parent_id')<span class="error">{{ $message }}</span>@enderror
                </div>
                <div class="field">
                    <label for="sort_order">Urutan tampil</label>
                    <input id="sort_order" type="number" name="sort_order" class="input" value="{{ old('sort_order', $member->sort_order ?? 0) }}" min="0" max="999">
                    <span class="hint">Angka kecil tampil lebih dahulu di tingkat yang sama.</span>
                </div>
            </div>
        </div>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary"><x-icon name="check" size="15px" /> Simpan</button>
        <a href="{{ route('admin.organization.index') }}" class="btn btn-ghost">Batal</a>
    </div>
</form>
@endsection
