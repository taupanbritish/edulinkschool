@extends('layouts.admin')

@section('title', $title)

@section('content')
<div class="page-head">
    <div>
        <h1>{{ $title }}</h1>
        <p>Lengkapi keterangan, gambar, dan video yang akan tampil di website sekolah.</p>
    </div>
    <div class="page-actions">
        <a href="{{ route($routePrefix.'.index') }}" class="btn btn-outline btn-sm"><x-icon name="arrow-left" size="14px" /> Kembali</a>
    </div>
</div>

<form method="POST" enctype="multipart/form-data" action="{{ $item->exists ? route($routePrefix.'.update', $item) : route($routePrefix.'.store') }}">
    @csrf
    @if($item->exists) @method('PUT') @endif

    <div class="section-split">
        <div class="stack">
            <div class="panel">
                <div class="panel-head"><h2>Informasi {{ $entityLabel }}</h2></div>
                <div class="panel-body">
                    <div class="field mb-2">
                        <label for="name">Nama</label>
                        <input id="name" name="name" class="input" value="{{ old('name', $item->name) }}" required>
                        @error('name')<span class="error">{{ $message }}</span>@enderror
                    </div>
                    <div class="field">
                        <label for="description">Keterangan</label>
                        <textarea id="description" name="description" class="textarea" style="min-height:200px" placeholder="Jelaskan fungsi, kapasitas, atau kegiatan…">{{ old('description', $item->description) }}</textarea>
                        @error('description')<span class="error">{{ $message }}</span>@enderror
                    </div>
                </div>
            </div>

            <div class="panel">
                <div class="panel-head"><h2>Gambar &amp; Video</h2></div>
                <div class="panel-body">
                    <div class="form-grid">
                        <x-photo-field :model="$item" label="Gambar {{ strtolower($entityLabel) }}" />

                        <div class="field full">
                            <label for="video">Berkas video</label>
                            @if($item->videoFileUrl())
                                <video controls preload="metadata" src="{{ $item->videoFileUrl() }}" style="width:100%;max-width:420px;border-radius:12px;border:1px solid var(--line)"></video>
                                <label class="checkbox mt-2"><input type="checkbox" name="remove_video" value="1"> Hapus berkas video saat ini</label>
                            @endif
                            <input id="video" type="file" name="video" class="input" accept="video/mp4,video/webm">
                            <span class="hint">MP4/WebM, maksimal 50 MB per video selama batas unggah server mencukupi.</span>
                            @error('video')<span class="error">{{ $message }}</span>@enderror
                        </div>

                        <div class="field full">
                            <label for="video_url">Tautan video YouTube</label>
                            <input id="video_url" name="video_url" class="input" value="{{ old('video_url', $item->video_url) }}" placeholder="https://youtu.be/xxxxxxxxxxx">
                            <span class="hint">Opsional. Jika diisi, video YouTube yang ditampilkan; kosongkan tautan untuk memakai berkas video.</span>
                            @error('video_url')<span class="error">{{ $message }}</span>@enderror
                        </div>
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
                        <input id="sort_order" type="number" name="sort_order" class="input" value="{{ old('sort_order', $item->sort_order ?? 0) }}" min="0" max="999">
                        <span class="hint">Angka kecil tampil lebih dahulu.</span>
                        @error('sort_order')<span class="error">{{ $message }}</span>@enderror
                    </div>
                    <input type="hidden" name="is_active" value="0">
                    <label class="checkbox"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $item->is_active ?? true))> Tampilkan di website</label>
                </div>
            </div>
            <div class="panel">
                <div class="panel-body">
                    <button type="submit" class="btn btn-primary btn-block"><x-icon name="check" size="15px" /> Simpan {{ $entityLabel }}</button>
                    <a href="{{ route($routePrefix.'.index') }}" class="btn btn-ghost btn-block mt-2">Batal</a>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection
