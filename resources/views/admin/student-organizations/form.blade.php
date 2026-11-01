@extends('layouts.admin')

@section('title', $organization->exists ? 'Ubah Organisasi Siswa' : 'Tambah Organisasi Siswa')

@section('content')
<div class="page-head">
    <div>
        <h1>{{ $organization->exists ? 'Ubah Organisasi Siswa' : 'Tambah Organisasi Siswa' }}</h1>
        <p>Lengkapi profil organisasi, visi, misi, dan program kerja.</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('admin.student-organizations.index') }}" class="btn btn-outline btn-sm"><x-icon name="arrow-left" size="14px" /> Kembali</a>
    </div>
</div>

<form method="POST" enctype="multipart/form-data" action="{{ $organization->exists ? route('admin.student-organizations.update', $organization) : route('admin.student-organizations.store') }}">
    @csrf
    @if($organization->exists) @method('PUT') @endif

    <div class="section-split">
        <div class="stack">
            <div class="panel">
                <div class="panel-head"><h2>Profil Organisasi</h2></div>
                <div class="panel-body">
                    <div class="form-grid">
                        <div class="field">
                            <label for="name">Nama organisasi</label>
                            <input id="name" name="name" class="input" value="{{ old('name', $organization->name) }}" required>
                            @error('name')<span class="error">{{ $message }}</span>@enderror
                        </div>
                        <div class="field">
                            <label for="short_name">Nama singkat</label>
                            <input id="short_name" name="short_name" class="input" value="{{ old('short_name', $organization->short_name) }}" placeholder="OSIS / MPK">
                            @error('short_name')<span class="error">{{ $message }}</span>@enderror
                        </div>
                        <div class="field full">
                            <label for="advisor">Pembina</label>
                            <input id="advisor" name="advisor" class="input" value="{{ old('advisor', $organization->advisor) }}">
                        </div>
                        <div class="field full">
                            <label for="description">Keterangan</label>
                            <textarea id="description" name="description" class="textarea">{{ old('description', $organization->description) }}</textarea>
                        </div>
                        <div class="field full">
                            <label for="vision">Visi</label>
                            <textarea id="vision" name="vision" class="textarea">{{ old('vision', $organization->vision) }}</textarea>
                        </div>
                        <div class="field full">
                            <label for="mission">Misi (satu poin per baris)</label>
                            <textarea id="mission" name="mission" class="textarea">{{ old('mission', $organization->mission) }}</textarea>
                        </div>
                        <div class="field full">
                            <label for="programs">Program kerja (satu per baris)</label>
                            <textarea id="programs" name="programs" class="textarea">{{ old('programs', $organization->programs) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="panel">
                <div class="panel-head"><h2>Foto Organisasi</h2></div>
                <div class="panel-body">
                    <div class="form-grid">
                        <x-photo-field :model="$organization" label="Foto organisasi" />
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
                        <input id="sort_order" type="number" name="sort_order" class="input" value="{{ old('sort_order', $organization->sort_order ?? 0) }}" min="0" max="999">
                    </div>
                    <input type="hidden" name="is_active" value="0">
                    <label class="checkbox"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $organization->is_active ?? true))> Tampilkan di website</label>
                </div>
            </div>
            <div class="panel">
                <div class="panel-body">
                    <button type="submit" class="btn btn-primary btn-block"><x-icon name="check" size="15px" /> Simpan Organisasi</button>
                    <a href="{{ route('admin.student-organizations.index') }}" class="btn btn-ghost btn-block mt-2">Batal</a>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection
