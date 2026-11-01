@extends('layouts.site')

@section('title', 'Kontak')

@section('content')
<section class="page-hero">
    <div class="container">
        <div class="breadcrumb">
            <a href="{{ route('home') }}">Beranda</a> <span>/</span> <span>Kontak</span>
        </div>
        <h1>Hubungi Kami</h1>
        <p>Kami siap membantu pertanyaan seputar akademik, administrasi, dan layanan sekolah.</p>
    </div>
</section>

<section class="section">
    <div class="container contact-grid">
        <div>
            <span class="eyebrow">Informasi Kontak</span>
            <h2>Tata usaha sekolah</h2>
            <p class="muted">Layanan administrasi dibuka pada hari kerja. Untuk urusan mendesak, silakan hubungi nomor WhatsApp sekolah.</p>

            <div class="contact-item">
                <div class="contact-icon"><x-icon name="map-pin" size="18px" /></div>
                <div>
                    <h4>Alamat</h4>
                    <p>{{ $school['address'] ?? '-' }}</p>
                </div>
            </div>
            <div class="contact-item">
                <div class="contact-icon"><x-icon name="phone" size="18px" /></div>
                <div>
                    <h4>Telepon</h4>
                    <p>{{ $school['phone'] ?? '-' }}</p>
                </div>
            </div>
            <div class="contact-item">
                <div class="contact-icon"><x-icon name="message" size="18px" /></div>
                <div>
                    <h4>WhatsApp</h4>
                    <p>{{ $school['whatsapp'] ?? '-' }}</p>
                </div>
            </div>
            <div class="contact-item">
                <div class="contact-icon"><x-icon name="mail" size="18px" /></div>
                <div>
                    <h4>Email</h4>
                    <p>{{ $school['email'] ?? '-' }}</p>
                </div>
            </div>
            <div class="contact-item">
                <div class="contact-icon"><x-icon name="clock" size="18px" /></div>
                <div>
                    <h4>Jam Layanan</h4>
                    <p>{{ $school['office_hours'] ?? '-' }}</p>
                </div>
            </div>

            <div class="mt-2" style="display:flex;gap:.6rem;flex-wrap:wrap">
                <a href="mailto:{{ $school['email'] ?? '' }}" class="btn btn-primary"><x-icon name="mail" size="15px" /> Kirim Email</a>
                <a href="tel:{{ preg_replace('/[^0-9+]/', '', $school['phone'] ?? '') }}" class="btn btn-outline"><x-icon name="phone" size="15px" /> Telepon</a>
            </div>
        </div>

        <div class="stack">
            <div class="map-card">
                <span class="badge badge-info" style="align-self:flex-start;margin-bottom:1rem">Lokasi</span>
                <h3>{{ $school['school_name'] ?? 'Sekolah' }}</h3>
                <p>{{ $school['address'] ?? '-' }}</p>
                <a href="{{ $school['maps_url'] ?? '#' }}" target="_blank" rel="noopener" class="btn btn-accent btn-sm" style="align-self:flex-start">
                    <x-icon name="map-pin" size="14px" /> Buka di Google Maps
                </a>
            </div>

            <div class="info-card">
                <h3><x-icon name="info" size="18px" /> Layanan Digital</h3>
                <p class="muted" style="font-size:.86rem">Siswa dan orang tua dapat mengakses jadwal, nilai, absensi, dan pengumuman melalui aplikasi mobile. Admin dan guru menggunakan portal akademik berbasis web.</p>
                <a href="{{ route('login') }}" class="btn btn-outline btn-sm">Masuk Portal Akademik</a>
            </div>
        </div>
    </div>
</section>

@php
    $socials = collect([
        ['icon' => 'instagram', 'label' => 'Instagram', 'url' => $school['social_instagram'] ?? null],
        ['icon' => 'youtube', 'label' => 'YouTube', 'url' => $school['social_youtube'] ?? null],
        ['icon' => 'facebook', 'label' => 'Facebook', 'url' => $school['social_facebook'] ?? null],
        ['icon' => 'tiktok', 'label' => 'TikTok', 'url' => $school['social_tiktok'] ?? null],
    ])->filter(fn (array $item) => filled($item['url']))->values();
@endphp

@if($socials->isNotEmpty())
<section class="section" style="padding-top:0">
    <div class="container">
        <div class="panel">
            <div class="panel-head"><h2>Media Sosial Resmi</h2></div>
            <div class="panel-body">
                <p class="muted" style="margin-top:0">Ikuti kanal resmi sekolah untuk kabar terbaru, dokumentasi kegiatan, dan karya siswa.</p>
                <div class="social-links">
                    @foreach($socials as $social)
                        <a class="social-link" href="{{ $social['url'] }}" target="_blank" rel="noopener">
                            <span class="social-icon"><x-icon :name="$social['icon']" size="18px" /></span>
                            <span>
                                <strong>{{ $social['label'] }}</strong>
                                <small>{{ parse_url($social['url'], PHP_URL_HOST) ?? $social['url'] }}</small>
                            </span>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>
@endif

<section class="section" id="kirim-pesan" style="padding-top:0">
    <div class="container">
        <div class="panel">
            <div class="panel-head"><h2><x-icon name="message" size="18px" /> Kirim Pesan / Saran</h2></div>
            <div class="panel-body">
                <p class="muted" style="margin-top:0">Sampaikan pertanyaan, masukan, atau saran Anda. Pesan akan diteruskan ke tata usaha sekolah dan ditindaklanjuti pada hari kerja.</p>

                @include('partials.flash')

                <form method="POST" action="{{ route('contact.send') }}">
                    @csrf
                    <input type="text" name="website" value="" tabindex="-1" autocomplete="off" style="position:absolute;left:-9999px" aria-hidden="true">

                    <div class="form-grid">
                        <div class="field">
                            <label for="contact_name">Nama</label>
                            <input id="contact_name" name="name" class="input" value="{{ old('name') }}" maxlength="100" required>
                            @error('name')<span class="error">{{ $message }}</span>@enderror
                        </div>
                        <div class="field">
                            <label for="contact_email">Email</label>
                            <input id="contact_email" type="email" name="email" class="input" value="{{ old('email') }}" maxlength="150" required>
                            @error('email')<span class="error">{{ $message }}</span>@enderror
                        </div>
                        <div class="field">
                            <label for="contact_phone">Nomor telepon / WhatsApp</label>
                            <input id="contact_phone" name="phone" class="input" value="{{ old('phone') }}" maxlength="30" placeholder="Opsional">
                            @error('phone')<span class="error">{{ $message }}</span>@enderror
                        </div>
                        <div class="field">
                            <label for="contact_subject">Subjek</label>
                            <input id="contact_subject" name="subject" class="input" value="{{ old('subject') }}" maxlength="150" required>
                            @error('subject')<span class="error">{{ $message }}</span>@enderror
                        </div>
                        <div class="field full">
                            <label for="contact_message">Pesan / saran</label>
                            <textarea id="contact_message" name="message" class="textarea" rows="5" maxlength="3000" required>{{ old('message') }}</textarea>
                            <span class="hint">Minimal 10 karakter, maksimal 3.000 karakter.</span>
                            @error('message')<span class="error">{{ $message }}</span>@enderror
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary"><x-icon name="arrow-right" size="15px" /> Kirim Pesan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>
@endsection
