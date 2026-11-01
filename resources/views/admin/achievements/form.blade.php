@extends('layouts.admin')

@section('title', $achievement->exists ? 'Ubah Prestasi' : 'Tambah Prestasi')

@section('content')
<div class="page-head">
    <div>
        <h1>{{ $achievement->exists ? 'Ubah Prestasi' : 'Tambah Prestasi' }}</h1>
        <p>Lengkapi data prestasi, penerima, foto, dan keterangan singkat.</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('admin.achievements.index') }}" class="btn btn-outline btn-sm"><x-icon name="arrow-left" size="14px" /> Kembali</a>
    </div>
</div>

<form method="POST" enctype="multipart/form-data" action="{{ $achievement->exists ? route('admin.achievements.update', $achievement) : route('admin.achievements.store') }}">
    @csrf
    @if($achievement->exists) @method('PUT') @endif

    <div class="section-split">
        <div class="stack">
            <div class="panel">
                <div class="panel-head"><h2>Informasi Prestasi</h2></div>
                <div class="panel-body">
                    <div class="form-grid">
                        <div class="field">
                            <label for="category">Kategori</label>
                            <select id="category" name="category" class="select">
                                @foreach(\App\Models\Achievement::CATEGORIES as $value => $label)
                                    <option value="{{ $value }}" @selected(old('category', $achievement->category) === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="field">
                            <label for="level">Tingkat</label>
                            <input id="level" name="level" class="input" value="{{ old('level', $achievement->level) }}" placeholder="Sekolah, Kota, Provinsi, Nasional, Internasional">
                        </div>
                        <div class="field full">
                            <label for="title">Judul prestasi</label>
                            <input id="title" name="title" class="input" value="{{ old('title', $achievement->title) }}" required>
                            @error('title')<span class="error">{{ $message }}</span>@enderror
                        </div>
                        <div class="field">
                            <label for="recipient">Penerima / tim</label>
                            <input id="recipient" name="recipient" class="input" value="{{ old('recipient', $achievement->recipient) }}">
                        </div>
                        <div class="field">
                            <label for="achieved_at">Tanggal prestasi</label>
                            <input id="achieved_at" type="date" name="achieved_at" class="input" value="{{ old('achieved_at', $achievement->achieved_at?->format('Y-m-d')) }}">
                        </div>
                        <div class="field full">
                            <label for="description">Keterangan</label>
                            <textarea id="description" name="description" class="textarea" style="min-height:160px">{{ old('description', $achievement->description) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="panel">
                <div class="panel-head"><h2>Foto Prestasi</h2></div>
                <div class="panel-body">
                    <div class="form-grid">
                        <x-photo-field :model="$achievement" label="Foto prestasi" />
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
                        <input id="sort_order" type="number" name="sort_order" class="input" value="{{ old('sort_order', $achievement->sort_order ?? 0) }}" min="0" max="999">
                    </div>
                    <input type="hidden" name="is_active" value="0">
                    <label class="checkbox"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $achievement->is_active ?? true))> Tampilkan di website</label>
                </div>
            </div>
            <div class="panel">
                <div class="panel-body">
                    <button type="submit" class="btn btn-primary btn-block"><x-icon name="check" size="15px" /> Simpan Prestasi</button>
                    <a href="{{ route('admin.achievements.index') }}" class="btn btn-ghost btn-block mt-2">Batal</a>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection
