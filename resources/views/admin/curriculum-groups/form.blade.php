@extends('layouts.admin')

@section('title', $group->exists ? 'Ubah Kelompok Kurikulum' : 'Tambah Kelompok Kurikulum')

@section('content')
<div class="page-head">
    <div>
        <h1>{{ $group->exists ? 'Ubah Kelompok Kurikulum' : 'Tambah Kelompok Kurikulum' }}</h1>
        <p>Kelompok mata pelajaran atau peminatan yang ditawarkan sekolah.</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('admin.curriculum-groups.index') }}" class="btn btn-outline btn-sm"><x-icon name="arrow-left" size="14px" /> Kembali</a>
    </div>
</div>

<form method="POST" action="{{ $group->exists ? route('admin.curriculum-groups.update', $group) : route('admin.curriculum-groups.store') }}">
    @csrf
    @if($group->exists) @method('PUT') @endif

    <div class="section-split">
        <div class="stack">
            <div class="panel">
                <div class="panel-head"><h2>Informasi Kelompok</h2></div>
                <div class="panel-body">
                    <div class="field mb-2">
                        <label for="name">Nama kelompok</label>
                        <input id="name" name="name" class="input" value="{{ old('name', $group->name) }}" required>
                        @error('name')<span class="error">{{ $message }}</span>@enderror
                    </div>
                    <div class="field mb-2">
                        <label for="description">Keterangan</label>
                        <textarea id="description" name="description" class="textarea">{{ old('description', $group->description) }}</textarea>
                    </div>
                    <div class="field">
                        <label for="subjects">Mata pelajaran (satu per baris)</label>
                        <textarea id="subjects" name="subjects" class="textarea" style="min-height:180px" placeholder="Matematika Lanjut&#10;Fisika&#10;Kimia">{{ old('subjects', $group->subjects) }}</textarea>
                        <span class="hint">Setiap baris ditampilkan sebagai satu mata pelajaran.</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="stack">
            <div class="panel">
                <div class="panel-head"><h2>Tampilan</h2></div>
                <div class="panel-body">
                    <div class="field mb-2">
                        <label for="sort_order">Urutan tampil</label>
                        <input id="sort_order" type="number" name="sort_order" class="input" value="{{ old('sort_order', $group->sort_order ?? 0) }}" min="0" max="999">
                    </div>
                    <input type="hidden" name="is_active" value="0">
                    <label class="checkbox"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $group->is_active ?? true))> Tampilkan di website</label>
                </div>
            </div>
            <div class="panel">
                <div class="panel-body">
                    <button type="submit" class="btn btn-primary btn-block"><x-icon name="check" size="15px" /> Simpan Kelompok</button>
                    <a href="{{ route('admin.curriculum-groups.index') }}" class="btn btn-ghost btn-block mt-2">Batal</a>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection
