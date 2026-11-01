@extends('layouts.admin')

@section('title', $writing->exists ? 'Ubah Artikel Siswa' : 'Tulis Artikel Siswa')

@section('content')
<div class="page-head">
    <div>
        <h1>{{ $writing->exists ? 'Ubah Artikel Siswa' : 'Tulis Artikel Siswa' }}</h1>
        <p>Karya yang diterbitkan akan tampil pada menu Berita &amp; Pengumuman &rarr; Majalah / Artikel Siswa.</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('admin.writings.index') }}" class="btn btn-outline btn-sm"><x-icon name="arrow-left" size="14px" /> Kembali</a>
    </div>
</div>

<form method="POST" enctype="multipart/form-data" action="{{ $writing->exists ? route('admin.writings.update', $writing) : route('admin.writings.store') }}">
    @csrf
    @if($writing->exists) @method('PUT') @endif

    <div class="section-split">
        <div class="stack">
            <div class="panel">
                <div class="panel-head"><h2>Isi Karya</h2></div>
                <div class="panel-body">
                    <div class="field mb-2">
                        <label for="title">Judul</label>
                        <input id="title" name="title" class="input" value="{{ old('title', $writing->title) }}" maxlength="200" required>
                        @error('title')<span class="error">{{ $message }}</span>@enderror
                    </div>
                    <div class="field mb-2">
                        <label for="excerpt">Ringkasan</label>
                        <input id="excerpt" name="excerpt" class="input" value="{{ old('excerpt', $writing->excerpt) }}" maxlength="255" placeholder="Satu kalimat ringkas untuk kartu karya">
                        @error('excerpt')<span class="error">{{ $message }}</span>@enderror
                    </div>
                    <div class="field">
                        <label for="body">Isi lengkap</label>
                        <textarea id="body" name="body" class="textarea" style="min-height:320px" required>{{ old('body', $writing->body) }}</textarea>
                        @error('body')<span class="error">{{ $message }}</span>@enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="stack">
            <div class="panel">
                <div class="panel-head"><h2>Penulis</h2></div>
                <div class="panel-body">
                    <div class="field mb-2">
                        <label for="author_name">Nama penulis</label>
                        <input id="author_name" name="author_name" class="input" value="{{ old('author_name', $writing->author_name) }}" maxlength="150" required>
                        @error('author_name')<span class="error">{{ $message }}</span>@enderror
                    </div>
                    <div class="field">
                        <label for="author_type">Jenis penulis</label>
                        <select id="author_type" name="author_type" class="select">
                            @foreach(\App\Models\StudentWriting::AUTHOR_TYPES as $value => $label)
                                <option value="{{ $value }}" @selected(old('author_type', $writing->author_type) === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('author_type')<span class="error">{{ $message }}</span>@enderror
                    </div>
                </div>
            </div>

            <div class="panel">
                <div class="panel-head"><h2>Publikasi</h2></div>
                <div class="panel-body">
                    <div class="field mb-2">
                        <label for="category">Kategori</label>
                        <select id="category" name="category" class="select">
                            @foreach(\App\Models\StudentWriting::CATEGORIES as $value => $label)
                                <option value="{{ $value }}" @selected(old('category', $writing->category) === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('category')<span class="error">{{ $message }}</span>@enderror
                    </div>
                    <div class="field mb-2">
                        <label for="published_at">Tanggal terbit</label>
                        <input id="published_at" type="datetime-local" name="published_at" class="input" value="{{ old('published_at', $writing->published_at?->format('Y-m-d\TH:i')) }}">
                        <span class="hint">Kosongkan untuk memakai waktu saat diterbitkan.</span>
                        @error('published_at')<span class="error">{{ $message }}</span>@enderror
                    </div>
                    <label class="checkbox">
                        <input type="checkbox" name="is_published" value="1" @checked(old('is_published', $writing->is_published))> Terbitkan sekarang
                    </label>
                </div>
            </div>

            <div class="panel">
                <div class="panel-head"><h2>Foto Sampul</h2></div>
                <div class="panel-body">
                    <x-photo-field :model="$writing" label="Foto sampul artikel" />
                </div>
            </div>

            <div class="panel">
                <div class="panel-body">
                    <button type="submit" class="btn btn-primary btn-block"><x-icon name="check" size="15px" /> Simpan Artikel</button>
                    <a href="{{ route('admin.writings.index') }}" class="btn btn-ghost btn-block mt-2">Batal</a>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection
