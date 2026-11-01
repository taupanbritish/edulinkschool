@extends('layouts.admin')

@section('title', $announcement->exists ? 'Ubah Artikel' : 'Tulis Artikel')

@section('content')
<div class="page-head">
    <div>
        <h1>{{ $announcement->exists ? 'Ubah Artikel' : 'Tulis Berita / Pengumuman' }}</h1>
        <p>Artikel yang diterbitkan akan tampil di website dan aplikasi mobile.</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('admin.announcements.index') }}" class="btn btn-outline btn-sm"><x-icon name="arrow-left" size="14px" /> Kembali</a>
    </div>
</div>

<form method="POST" enctype="multipart/form-data" action="{{ $announcement->exists ? route('admin.announcements.update', $announcement) : route('admin.announcements.store') }}">
    @csrf
    @if($announcement->exists) @method('PUT') @endif

    <div class="section-split">
        <div class="stack">
            <div class="panel">
                <div class="panel-head"><h2>Isi Artikel</h2></div>
                <div class="panel-body">
                    <div class="field mb-2">
                        <label for="title">Judul</label>
                        <input id="title" name="title" class="input" value="{{ old('title', $announcement->title) }}" required>
                        @error('title')<span class="error">{{ $message }}</span>@enderror
                    </div>
                    <div class="field mb-2">
                        <label for="excerpt">Ringkasan</label>
                        <input id="excerpt" name="excerpt" class="input" value="{{ old('excerpt', $announcement->excerpt) }}" placeholder="Satu kalimat ringkas untuk kartu berita">
                        @error('excerpt')<span class="error">{{ $message }}</span>@enderror
                    </div>
                    <div class="field">
                        <label for="body">Isi lengkap</label>
                        <textarea id="body" name="body" class="textarea" style="min-height:320px" required>{{ old('body', $announcement->body) }}</textarea>
                        @error('body')<span class="error">{{ $message }}</span>@enderror
                    </div>
                </div>
            </div>

            <div class="panel">
                <div class="panel-head">
                    <h2>Galeri Foto &amp; Video</h2>
                    @if($announcement->exists && $announcement->attachments->isNotEmpty())
                        <span class="muted" style="font-size:.8rem">{{ $announcement->attachments->count() }} lampiran</span>
                    @endif
                </div>
                <div class="panel-body">
                    @if($announcement->exists && $announcement->attachments->isNotEmpty())
                        <div class="attachment-grid mb-2">
                            @foreach($announcement->attachments as $attachment)
                                <div class="attachment-card">
                                    <div class="attachment-thumb">
                                        @if($attachment->isPhoto())
                                            <img src="{{ $attachment->mediaUrl() }}" alt="{{ $attachment->caption ?: 'Foto lampiran' }}" loading="lazy">
                                        @else
                                            <span class="attachment-placeholder"><x-icon name="video" size="22px" /> {{ $attachment->embedUrl() ? 'YouTube' : 'Video' }}</span>
                                        @endif
                                        <span class="attachment-type">{{ $attachment->typeLabel() }}</span>
                                    </div>
                                    @if($attachment->isVideo())
                                        <span class="attachment-file">{{ $attachment->embedUrl() ?? $attachment->filename() }}</span>
                                    @endif
                                    <div class="field">
                                        <label for="caption-{{ $attachment->id }}">Keterangan</label>
                                        <input id="caption-{{ $attachment->id }}" name="captions[{{ $attachment->id }}]" class="input" value="{{ old('captions.'.$attachment->id, $attachment->caption) }}" maxlength="150" placeholder="Opsional">
                                    </div>
                                    <label class="checkbox attachment-remove">
                                        <input type="checkbox" name="remove_attachments[]" value="{{ $attachment->id }}" @checked(in_array($attachment->id, (array) old('remove_attachments', []), true))> Hapus lampiran
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <div class="form-grid">
                        <div class="field">
                            <label for="photos">Tambah foto</label>
                            <input id="photos" type="file" name="photos[]" class="input" accept="image/jpeg,image/png,image/webp" multiple>
                            <span class="hint">JPG/PNG/WebP, maksimal 4 MB per foto, hingga 12 foto sekali unggah.</span>
                            @error('photos')<span class="error">{{ $message }}</span>@enderror
                            @error('photos.*')<span class="error">{{ $message }}</span>@enderror
                        </div>
                        <div class="field">
                            <label for="videos">Tambah video</label>
                            <input id="videos" type="file" name="videos[]" class="input" accept="video/mp4,video/webm" multiple>
                            <span class="hint">MP4/WebM, maksimal 50 MB per video selama batas unggah server mencukupi.</span>
                            @error('videos')<span class="error">{{ $message }}</span>@enderror
                            @error('videos.*')<span class="error">{{ $message }}</span>@enderror
                        </div>
                    </div>
                    <div class="field mt-2">
                        <label for="video_urls">Tautan video YouTube</label>
                        <textarea id="video_urls" name="video_urls" class="textarea" rows="2" placeholder="https://youtu.be/xxxxxxxxxxx&#10;https://www.youtube.com/watch?v=xxxxxxxxxxx">{{ old('video_urls') }}</textarea>
                        <span class="hint">Satu tautan per baris, maksimal 6 tautan. Cocok untuk video tanpa unggah berkas.</span>
                        @error('video_urls')<span class="error">{{ $message }}</span>@enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="stack">
            <div class="panel">
                <div class="panel-head"><h2>Publikasi</h2></div>
                <div class="panel-body">
                    <div class="field mb-2">
                        <label for="category">Kategori</label>
                        <select id="category" name="category" class="select">
                            @foreach(\App\Models\Announcement::CATEGORIES as $value => $label)
                                <option value="{{ $value }}" @selected(old('category', $announcement->category) === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="field mb-2">
                        <label for="published_at">Tanggal terbit</label>
                        <input id="published_at" type="datetime-local" name="published_at" class="input" value="{{ old('published_at', $announcement->published_at?->format('Y-m-d\TH:i')) }}">
                        <span class="hint">Kosongkan untuk memakai waktu saat diterbitkan.</span>
                    </div>
                    <label class="checkbox">
                        <input type="checkbox" name="is_published" value="1" @checked(old('is_published', $announcement->is_published))> Terbitkan sekarang
                    </label>
                </div>
            </div>
            <div class="panel">
                <div class="panel-body">
                    <button type="submit" class="btn btn-primary btn-block"><x-icon name="check" size="15px" /> Simpan Artikel</button>
                    <a href="{{ route('admin.announcements.index') }}" class="btn btn-ghost btn-block mt-2">Batal</a>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection
