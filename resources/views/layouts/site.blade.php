@php
    $themeAccents = [
        ['key' => 'biru', 'label' => 'Biru Edukasi', 'color' => '#17496e'],
        ['key' => 'botanical', 'label' => 'Hijau Marun', 'color' => '#1c4a39', 'title' => 'Hijau Marun / Botanical'],
        ['key' => 'karamel', 'label' => 'Merah Karamel', 'color' => '#7a1f1a'],
        ['key' => 'ungu', 'label' => 'Ungu Modern', 'color' => '#3b1d6e'],
        ['key' => 'toska', 'label' => 'Toska Samudra', 'color' => '#0f4c52'],
    ];
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', $school['school_name'] ?? 'Sekolah') — {{ $school['school_short'] ?? '' }}</title>
    <meta name="description" content="@yield('description', $school['tagline'] ?? 'Website resmi sekolah')">
    <x-favicon />
    <script src="{{ asset('js/theme.js') }}"></script>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
    <div class="topbar">
        <div class="container topbar-inner">
            <div class="meta topbar-contact">
                <span><x-icon name="phone" size="13px" /> {{ $school['phone'] ?? '-' }}</span>
                <span><x-icon name="mail" size="13px" /> {{ $school['email'] ?? '-' }}</span>
            </div>
            <form class="topbar-search" role="search" action="{{ route('search') }}" method="GET">
                <x-icon name="search" size="14px" />
                <input type="search" name="q" value="{{ request()->routeIs('search') ? request('q') : '' }}" placeholder="Cari berita, artikel, guru, fasilitas…" aria-label="Pencarian cepat">
                <button type="submit">Cari</button>
            </form>
            <div class="meta topbar-hours">
                <span><x-icon name="clock" size="13px" /> {{ $school['office_hours'] ?? 'Senin – Jumat, 07.00 – 15.00 WIB' }}</span>
                <a href="{{ auth()->check() ? route('dashboard') : route('login') }}">{{ auth()->check() ? 'Buka Panel' : 'Portal Akademik' }}</a>
            </div>
        </div>
    </div>

    <header class="site-header">
        <div class="container">
            <a class="brand" href="{{ route('home') }}">
                <x-brand-mark />
                <div class="brand-text">
                    <strong>{{ $school['school_name'] ?? 'Sekolah' }}</strong>
                    <span>{{ $school['tagline'] ?? 'Website Resmi Sekolah' }}</span>
                </div>
            </a>

            <nav class="site-nav" id="siteNav">
                <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'active' : '' }}">Beranda</a>
                <div class="nav-drop {{ request()->routeIs('profile', 'teachers', 'organization', 'staff', 'facilities') ? 'active' : '' }}">
                    <a href="{{ route('profile') }}" class="{{ request()->routeIs('profile') ? 'active' : '' }}">
                        Profil Sekolah <x-icon name="chevron-down" size="14px" />
                    </a>
                    <div class="nav-drop-menu">
                        <a href="{{ route('organization') }}" class="{{ request()->routeIs('organization') ? 'active' : '' }}">Struktur Organisasi</a>
                        <a href="{{ route('teachers') }}" class="{{ request()->routeIs('teachers') ? 'active' : '' }}">Guru</a>
                        <a href="{{ route('staff') }}" class="{{ request()->routeIs('staff') ? 'active' : '' }}">Tata Usaha</a>
                        <a href="{{ route('facilities') }}" class="{{ request()->routeIs('facilities') ? 'active' : '' }}">Fasilitas</a>
                    </div>
                </div>
                <div class="nav-drop {{ request()->routeIs('curriculum', 'academic-calendar', 'alumni', 'achievements.*') ? 'active' : '' }}">
                    <a href="{{ route('curriculum') }}" class="{{ request()->routeIs('curriculum') ? 'active' : '' }}">
                        Akademik <x-icon name="chevron-down" size="14px" />
                    </a>
                    <div class="nav-drop-menu">
                        <a href="{{ route('curriculum') }}" class="{{ request()->routeIs('curriculum') ? 'active' : '' }}">Kurikulum</a>
                        <a href="{{ route('academic-calendar') }}" class="{{ request()->routeIs('academic-calendar') ? 'active' : '' }}">Kalender Akademik</a>
                        <a href="{{ route('alumni') }}" class="{{ request()->routeIs('alumni') ? 'active' : '' }}">Info PTN / Alumni</a>
                        <span class="nav-drop-label">Prestasi</span>
                        <a href="{{ route('achievements.academic') }}" class="{{ request()->routeIs('achievements.academic') ? 'active' : '' }}">Prestasi Akademik</a>
                        <a href="{{ route('achievements.non_academic') }}" class="{{ request()->routeIs('achievements.non_academic') ? 'active' : '' }}">Prestasi Non Akademik</a>
                    </div>
                </div>
                <div class="nav-drop {{ request()->routeIs('extracurriculars', 'student-organizations', 'counseling') ? 'active' : '' }}">
                    <a href="{{ route('extracurriculars') }}" class="{{ request()->routeIs('extracurriculars') ? 'active' : '' }}">
                        Kesiswaan &amp; Ekstrakurikuler <x-icon name="chevron-down" size="14px" />
                    </a>
                    <div class="nav-drop-menu">
                        <a href="{{ route('extracurriculars') }}" class="{{ request()->routeIs('extracurriculars') ? 'active' : '' }}">Ekstrakurikuler</a>
                        <a href="{{ route('student-organizations') }}" class="{{ request()->routeIs('student-organizations') ? 'active' : '' }}">Organisasi Siswa</a>
                        <a href="{{ route('counseling') }}" class="{{ request()->routeIs('counseling') ? 'active' : '' }}">Bimbingan Konseling</a>
                    </div>
                </div>
                <div class="nav-drop {{ request()->routeIs('news.*', 'writings.*') ? 'active' : '' }}">
                    <a href="{{ route('news.index') }}" class="{{ request()->routeIs('news.*') ? 'active' : '' }}">
                        Berita &amp; Pengumuman <x-icon name="chevron-down" size="14px" />
                    </a>
                    <div class="nav-drop-menu">
                        <a href="{{ route('news.index') }}" class="{{ request()->routeIs('news.*') ? 'active' : '' }}">Berita &amp; Pengumuman</a>
                        <a href="{{ route('writings.index') }}" class="{{ request()->routeIs('writings.*') ? 'active' : '' }}">Majalah / Artikel Siswa</a>
                    </div>
                </div>
                <a href="{{ route('contact') }}" class="{{ request()->routeIs('contact') ? 'active' : '' }}">Kontak</a>
                <a href="{{ auth()->check() ? route('dashboard') : route('login') }}" class="btn btn-primary btn-sm" style="margin-left:.4rem">
                    <x-icon name="lock" size="14px" /> Portal
                </a>
            </nav>

            <div class="header-actions">
                <button class="nav-toggle" type="button" aria-label="Buka menu" onclick="document.getElementById('siteNav').classList.toggle('open')">
                    <x-icon name="menu" />
                </button>

                <div class="theme-picker" data-theme-picker>
                    <button type="button" class="theme-toggle" data-theme-toggle aria-expanded="false" aria-controls="themePanel" aria-label="Pengaturan tema tampilan">
                        <x-icon name="palette" />
                    </button>
                    <div class="theme-panel" id="themePanel" data-theme-panel hidden aria-labelledby="themePanelTitle">
                        <div class="theme-panel-head">
                            <strong id="themePanelTitle">Tema Tampilan</strong>
                            <span>Warna aksen dan mode disimpan di perangkat ini.</span>
                        </div>
                        <div class="theme-section">
                            <span class="theme-label">Warna aksen</span>
                            <div class="theme-swatches">
                                @foreach($themeAccents as $accent)
                                    <button type="button" class="theme-swatch" data-accent-choice="{{ $accent['key'] }}" style="--swatch:{{ $accent['color'] }}" title="{{ $accent['title'] ?? $accent['label'] }}">
                                        <span class="theme-swatch-dot"></span>
                                        <small>{{ $accent['label'] }}</small>
                                    </button>
                                @endforeach
                                <button type="button" class="theme-swatch theme-swatch-custom" data-accent-choice="custom" title="Pilih warna sendiri">
                                    <span class="theme-swatch-dot"></span>
                                    <small>Kustom</small>
                                </button>
                            </div>
                            <label class="theme-custom-row">
                                Warna khusus
                                <input type="color" data-theme-color value="#17496e" aria-label="Pilih warna aksen khusus">
                            </label>
                        </div>
                        <div class="theme-section">
                            <span class="theme-label">Mode tampilan</span>
                            <div class="theme-modes">
                                <button type="button" class="theme-mode" data-mode-choice="light"><x-icon name="sun" /><span>Terang</span></button>
                                <button type="button" class="theme-mode" data-mode-choice="dark"><x-icon name="moon" /><span>Gelap</span></button>
                                <button type="button" class="theme-mode" data-mode-choice="system"><x-icon name="monitor" /><span>Sistem</span></button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <main>
        @yield('content')
    </main>

    <footer class="site-footer">
        <div class="container">
            <div class="footer-grid">
                <div>
                    <div class="footer-brand">
                        <x-brand-mark />
                        <strong>{{ $school['school_name'] ?? 'Sekolah' }}</strong>
                    </div>
                    <p>{{ $school['tagline'] ?? '' }}. NPSN {{ $school['npsn'] ?? '-' }} · Akreditasi {{ $school['accreditation'] ?? '-' }}.</p>
                    <p>{{ $school['address'] ?? '' }}</p>
                </div>
                <div>
                    <h4>Jelajahi</h4>
                    <div class="footer-links">
                        <a href="{{ route('home') }}">Beranda</a>
                        <a href="{{ route('profile') }}">Profil Sekolah</a>
                        <a href="{{ route('organization') }}">Struktur Organisasi</a>
                        <a href="{{ route('teachers') }}">Guru</a>
                        <a href="{{ route('staff') }}">Tata Usaha</a>
                        <a href="{{ route('facilities') }}">Fasilitas</a>
                        <a href="{{ route('curriculum') }}">Kurikulum</a>
                        <a href="{{ route('academic-calendar') }}">Kalender Akademik</a>
                        <a href="{{ route('alumni') }}">Info PTN / Alumni</a>
                        <a href="{{ route('achievements.academic') }}">Prestasi Akademik</a>
                        <a href="{{ route('achievements.non_academic') }}">Prestasi Non Akademik</a>
                        <a href="{{ route('extracurriculars') }}">Ekstrakurikuler</a>
                        <a href="{{ route('student-organizations') }}">Organisasi Siswa</a>
                        <a href="{{ route('counseling') }}">Bimbingan Konseling</a>
                        <a href="{{ route('news.index') }}">Berita</a>
                        <a href="{{ route('writings.index') }}">Majalah / Artikel Siswa</a>
                        <a href="{{ route('contact') }}">Kontak</a>
                    </div>
                </div>
                <div>
                    <h4>Portal</h4>
                    <div class="footer-links">
                        <a href="{{ route('login') }}">Login Admin & Guru</a>
                        <span>Aplikasi mobile untuk siswa dan orang tua tersedia untuk Android & iOS.</span>
                    </div>
                </div>
                <div>
                    <h4>Hubungi Kami</h4>
                    <div class="footer-links">
                        <span>{{ $school['phone'] ?? '-' }}</span>
                        <span>{{ $school['whatsapp'] ?? '-' }} (WhatsApp)</span>
                        <span>{{ $school['email'] ?? '-' }}</span>
                        <span>{{ $school['office_hours'] ?? '' }}</span>
                    </div>
                </div>
            </div>
            <div class="footer-bottom">
                <span>&copy; {{ date('Y') }} {{ $school['school_name'] ?? 'Sekolah' }}. Seluruh hak cipta dilindungi.</span>
                <span>Sistem Informasi Sekolah · {{ $school['school_short'] ?? $school['school_name'] ?? 'Sekolah' }}</span>
            </div>
        </div>
    </footer>
</body>
</html>
