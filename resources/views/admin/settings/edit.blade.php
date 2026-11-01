@extends('layouts.admin')

@section('title', 'Pengaturan Sekolah')

@section('content')
<div class="page-head">
    <div>
        <h1>Pengaturan Sekolah</h1>
        <p>Identitas, kontak, dan konten profil yang tampil di website.</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('home') }}" target="_blank" rel="noopener" class="btn btn-outline btn-sm">Pratinjau Website</a>
    </div>
</div>

<form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    @php
        $logoUrl = \App\Support\Media::url($values['logo_path'] ?? null);
        $faviconUrl = \App\Support\Media::url($values['favicon_path'] ?? null);
        $principalPhotoUrl = \App\Support\Media::url($values['principal_photo_path'] ?? null);
    @endphp

    <div class="stack">
        <div class="panel">
            <div class="panel-head"><h2>Identitas Sekolah</h2></div>
            <div class="panel-body">
                <div class="form-grid">
                    <div class="field">
                        <label for="school_name">Nama sekolah</label>
                        <input id="school_name" name="school_name" class="input" value="{{ old('school_name', $values['school_name'] ?? '') }}" required>
                    </div>
                    <div class="field">
                        <label for="school_short">Nama singkat</label>
                        <input id="school_short" name="school_short" class="input" value="{{ old('school_short', $values['school_short'] ?? '') }}" required>
                    </div>
                    <div class="field full">
                        <label for="tagline">Tagline</label>
                        <input id="tagline" name="tagline" class="input" value="{{ old('tagline', $values['tagline'] ?? '') }}">
                    </div>
                    <div class="field full">
                        <label for="hero_title">Judul hero beranda</label>
                        <input id="hero_title" name="hero_title" class="input" value="{{ old('hero_title', $values['hero_title'] ?? '') }}" maxlength="150">
                        <span class="hint">Tampil sebagai judul besar di beranda. Bungkus kata dengan *tanda bintang* untuk memberi warna aksen, misalnya Membentuk Generasi *Berprestasi* &amp; Berkarakter.</span>
                        @error('hero_title')<span class="error">{{ $message }}</span>@enderror
                    </div>
                    <div class="field full">
                        <label for="hero_subtitle">Subjudul hero beranda</label>
                        <textarea id="hero_subtitle" name="hero_subtitle" class="textarea" maxlength="400">{{ old('hero_subtitle', $values['hero_subtitle'] ?? '') }}</textarea>
                        <span class="hint">Paragraf pengantar di bawah judul hero. Kosongkan untuk memakai tagline diikuti kalimat bawaan.</span>
                        @error('hero_subtitle')<span class="error">{{ $message }}</span>@enderror
                    </div>
                    <div class="field">
                        <label for="npsn">NPSN</label>
                        <input id="npsn" name="npsn" class="input" value="{{ old('npsn', $values['npsn'] ?? '') }}">
                    </div>
                    <div class="field">
                        <label for="accreditation">Akreditasi</label>
                        <input id="accreditation" name="accreditation" class="input" value="{{ old('accreditation', $values['accreditation'] ?? '') }}">
                    </div>
                    <div class="field">
                        <label for="founded_year">Tahun berdiri</label>
                        <input id="founded_year" name="founded_year" class="input" value="{{ old('founded_year', $values['founded_year'] ?? '') }}" maxlength="4">
                    </div>
                </div>
            </div>
        </div>

        <div class="panel">
            <div class="panel-head"><h2>Logo &amp; Favicon</h2></div>
            <div class="panel-body">
                <div class="form-grid">
                    <div class="field">
                        <label for="logo">Logo sekolah</label>
                        <div class="photo-field">
                            @if($logoUrl)
                                <img src="{{ $logoUrl }}" alt="Logo sekolah" class="photo-preview brand-preview">
                            @else
                                <div class="photo-preview photo-preview-empty"><x-icon name="image" size="24px" /></div>
                            @endif
                            <div class="photo-controls">
                                <input id="logo" type="file" name="logo" accept="image/jpeg,image/png,image/webp">
                                <span class="hint">JPG, PNG, atau WEBP. Maksimal 2 MB. Tampil di header website, panel admin, dan halaman login.</span>
                                @if($logoUrl)
                                    <label class="checkbox"><input type="checkbox" name="remove_logo" value="1"> Hapus logo saat ini</label>
                                @endif
                                @error('logo')<span class="error">{{ $message }}</span>@enderror
                            </div>
                        </div>
                    </div>

                    <div class="field">
                        <label for="favicon">Favicon</label>
                        <div class="photo-field">
                            @if($faviconUrl)
                                <img src="{{ $faviconUrl }}" alt="Favicon sekolah" class="photo-preview favicon-preview">
                            @else
                                <div class="photo-preview photo-preview-empty"><x-icon name="image" size="24px" /></div>
                            @endif
                            <div class="photo-controls">
                                <input id="favicon" type="file" name="favicon" accept="image/png,image/x-icon,image/vnd.microsoft.icon,image/jpeg,image/webp">
                                <span class="hint">PNG atau ICO. Maksimal 1 MB. Tampil pada tab peramban di website dan portal.</span>
                                @if($faviconUrl)
                                    <label class="checkbox"><input type="checkbox" name="remove_favicon" value="1"> Hapus favicon saat ini</label>
                                @endif
                                @error('favicon')<span class="error">{{ $message }}</span>@enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="panel">
            <div class="panel-head"><h2>Kontak &amp; Lokasi</h2></div>
            <div class="panel-body">
                <div class="form-grid">
                    <div class="field full">
                        <label for="address">Alamat</label>
                        <input id="address" name="address" class="input" value="{{ old('address', $values['address'] ?? '') }}">
                    </div>
                    <div class="field">
                        <label for="phone">Telepon</label>
                        <input id="phone" name="phone" class="input" value="{{ old('phone', $values['phone'] ?? '') }}">
                    </div>
                    <div class="field">
                        <label for="whatsapp">WhatsApp</label>
                        <input id="whatsapp" name="whatsapp" class="input" value="{{ old('whatsapp', $values['whatsapp'] ?? '') }}">
                    </div>
                    <div class="field">
                        <label for="email">Email</label>
                        <input id="email" type="email" name="email" class="input" value="{{ old('email', $values['email'] ?? '') }}">
                    </div>
                    <div class="field">
                        <label for="office_hours">Jam layanan</label>
                        <input id="office_hours" name="office_hours" class="input" value="{{ old('office_hours', $values['office_hours'] ?? '') }}">
                    </div>
                    <div class="field full">
                        <label for="maps_url">Tautan Google Maps</label>
                        <input id="maps_url" name="maps_url" class="input" value="{{ old('maps_url', $values['maps_url'] ?? '') }}" placeholder="https://maps.google.com/?q=...">
                    </div>
                </div>
            </div>
        </div>

        <div class="panel">
            <div class="panel-head"><h2>Media Sosial</h2></div>
            <div class="panel-body">
                <div class="form-grid">
                    <div class="field">
                        <label for="social_instagram">Instagram</label>
                        <input id="social_instagram" name="social_instagram" class="input" value="{{ old('social_instagram', $values['social_instagram'] ?? '') }}" placeholder="https://www.instagram.com/...">
                        @error('social_instagram')<span class="error">{{ $message }}</span>@enderror
                    </div>
                    <div class="field">
                        <label for="social_youtube">YouTube</label>
                        <input id="social_youtube" name="social_youtube" class="input" value="{{ old('social_youtube', $values['social_youtube'] ?? '') }}" placeholder="https://www.youtube.com/@...">
                        @error('social_youtube')<span class="error">{{ $message }}</span>@enderror
                    </div>
                    <div class="field">
                        <label for="social_facebook">Facebook</label>
                        <input id="social_facebook" name="social_facebook" class="input" value="{{ old('social_facebook', $values['social_facebook'] ?? '') }}" placeholder="https://www.facebook.com/...">
                        @error('social_facebook')<span class="error">{{ $message }}</span>@enderror
                    </div>
                    <div class="field">
                        <label for="social_tiktok">TikTok</label>
                        <input id="social_tiktok" name="social_tiktok" class="input" value="{{ old('social_tiktok', $values['social_tiktok'] ?? '') }}" placeholder="https://www.tiktok.com/@...">
                        @error('social_tiktok')<span class="error">{{ $message }}</span>@enderror
                    </div>
                </div>
                <span class="hint">Tautan tampil pada halaman Kontak. Kosongkan kolom untuk menyembunyikan kanal tersebut.</span>
            </div>
        </div>

        <div class="panel">
            <div class="panel-head"><h2>Profil &amp; Sambutan</h2></div>
            <div class="panel-body">
                <div class="form-grid">
                    <div class="field">
                        <label for="principal_name">Nama kepala sekolah</label>
                        <input id="principal_name" name="principal_name" class="input" value="{{ old('principal_name', $values['principal_name'] ?? '') }}">
                    </div>
                    <div class="field full">
                        <label for="principal_photo">Foto kepala sekolah</label>
                        <div class="photo-field">
                            @if($principalPhotoUrl)
                                <img src="{{ $principalPhotoUrl }}" alt="Foto kepala sekolah" class="photo-preview">
                            @else
                                <div class="photo-preview photo-preview-empty"><x-icon name="image" size="24px" /></div>
                            @endif
                            <div class="photo-controls">
                                <input id="principal_photo" type="file" name="principal_photo" accept="image/jpeg,image/png,image/webp">
                                <span class="hint">JPG, PNG, atau WEBP. Maksimal 2 MB. Tampil pada kartu kepala sekolah di beranda website.</span>
                                @if($principalPhotoUrl)
                                    <label class="checkbox"><input type="checkbox" name="remove_principal_photo" value="1"> Hapus foto saat ini</label>
                                @endif
                                @error('principal_photo')<span class="error">{{ $message }}</span>@enderror
                            </div>
                        </div>
                    </div>
                    <div class="field full">
                        <label for="principal_welcome">Sambutan kepala sekolah</label>
                        <textarea id="principal_welcome" name="principal_welcome" class="textarea">{{ old('principal_welcome', $values['principal_welcome'] ?? '') }}</textarea>
                    </div>
                    <div class="field full">
                        <label for="vision">Visi</label>
                        <textarea id="vision" name="vision" class="textarea">{{ old('vision', $values['vision'] ?? '') }}</textarea>
                    </div>
                    <div class="field full">
                        <label for="mission">Misi (satu poin per baris)</label>
                        <textarea id="mission" name="mission" class="textarea">{{ old('mission', $values['mission'] ?? '') }}</textarea>
                    </div>
                    <div class="field full">
                        <label for="history">Sejarah singkat</label>
                        <textarea id="history" name="history" class="textarea">{{ old('history', $values['history'] ?? '') }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="panel">
            <div class="panel-head"><h2>Video Beranda</h2></div>
            <div class="panel-body">
                <div class="field">
                    <label for="youtube_url">Tautan video YouTube</label>
                    <input id="youtube_url" name="youtube_url" class="input" value="{{ old('youtube_url', $values['youtube_url'] ?? '') }}" placeholder="https://www.youtube.com/watch?v=...">
                    <span class="hint">Tempel tautan YouTube (watch, youtu.be, atau shorts). Video tampil di beranda setelah bagian pengumuman. Kosongkan untuk menyembunyikan video.</span>
                    @error('youtube_url')<span class="error">{{ $message }}</span>@enderror
                </div>
            </div>
        </div>

        <div class="panel">
            <div class="panel-head"><h2>Kurikulum</h2></div>
            <div class="panel-body">
                <div class="form-grid">
                    <div class="field full">
                        <label for="curriculum_name">Nama kurikulum</label>
                        <input id="curriculum_name" name="curriculum_name" class="input" value="{{ old('curriculum_name', $values['curriculum_name'] ?? '') }}" placeholder="Kurikulum Merdeka">
                        <span class="hint">Tampil sebagai judul pada halaman Akademik &rarr; Kurikulum.</span>
                        @error('curriculum_name')<span class="error">{{ $message }}</span>@enderror
                    </div>
                    <div class="field full">
                        <label for="curriculum_description">Penjelasan kurikulum</label>
                        <textarea id="curriculum_description" name="curriculum_description" class="textarea">{{ old('curriculum_description', $values['curriculum_description'] ?? '') }}</textarea>
                        <span class="hint">Ringkasan sistem kurikulum yang diterapkan. Kelompok mata pelajaran dikelola melalui menu Kelompok Mapel.</span>
                        @error('curriculum_description')<span class="error">{{ $message }}</span>@enderror
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary"><x-icon name="check" size="15px" /> Simpan Pengaturan</button>
    </div>
</form>
@endsection
