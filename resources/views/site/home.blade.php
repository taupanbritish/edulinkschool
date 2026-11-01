@extends('layouts.site')

@section('title', 'Beranda')
@section('description', $school['tagline'] ?? 'Website resmi sekolah')

@section('content')
@php
    $heroTitle = trim($school['hero_title'] ?? '') ?: 'Membentuk Generasi *Berprestasi* & Berkarakter';
    $heroTitleHtml = preg_replace('/\*(.+?)\*/', '<span class="highlight">$1</span>', e($heroTitle));
    $heroSubtitle = trim($school['hero_subtitle'] ?? '');
    if ($heroSubtitle === '') {
        $heroTagline = trim($school['tagline'] ?? '');
        $heroSubtitle = ($heroTagline !== '' ? $heroTagline.'. ' : '').'Kami menghadirkan pembelajaran bermakna, lingkungan yang aman, dan kemitraan erat antara sekolah, orang tua, dan masyarakat.';
    }
@endphp
<section class="hero">
    <div class="container">
        <div>
            <span class="hero-badge"><x-icon name="award" size="14px" /> Akreditasi {{ $school['accreditation'] ?? 'A' }} · NPSN {{ $school['npsn'] ?? '-' }}</span>
            <h1>{!! $heroTitleHtml !!}</h1>
            <p class="lead">{{ $heroSubtitle }}</p>
            <div class="hero-actions">
                <a href="{{ route('profile') }}" class="btn btn-accent">
                    Jelajahi Profil Sekolah <x-icon name="arrow-right" size="16px" />
                </a>
                <a href="{{ route('news.index') }}" class="btn btn-outline" style="background:transparent;color:#fff;border-color:rgba(255,255,255,.3)">
                    Berita Terbaru
                </a>
            </div>
            <div class="hero-note">
                <x-icon name="info" size="14px" /> Siswa dan orang tua dapat memantau jadwal, nilai, dan absensi melalui aplikasi mobile sekolah.
            </div>
        </div>

        <aside class="hero-panel">
            <h3>Sekolah dalam Angka</h3>
            <div class="stat-grid">
                <div class="hero-stat">
                    <strong>{{ number_format($stats['students'], 0, ',', '.') }}</strong>
                    <span>Siswa aktif</span>
                </div>
                <div class="hero-stat">
                    <strong>{{ number_format($stats['teachers'], 0, ',', '.') }}</strong>
                    <span>Guru</span>
                </div>
                <div class="hero-stat">
                    <strong>{{ number_format($stats['staff'], 0, ',', '.') }}</strong>
                    <span>Tata Usaha</span>
                </div>
                <div class="hero-stat">
                    <strong>{{ number_format($stats['classrooms'], 0, ',', '.') }}</strong>
                    <span>Rombongan belajar</span>
                </div>
            </div>
            <div class="panel-foot">
                <span>Tahun Pelajaran 2026/2027</span>
                <span class="badge badge-success">Pendaftaran Aktif</span>
            </div>
        </aside>
    </div>
</section>

<section class="section alt">
    <div class="container">
        <div class="section-head">
            <span class="eyebrow">Mengapa Kami</span>
            <h2>Lingkungan belajar yang menumbuhkan potensi</h2>
            <p>Tiga pilar utama yang kami jaga setiap hari untuk mendukung perkembangan akademik dan karakter siswa.</p>
        </div>

        <div class="grid grid-3">
            <article class="feature-card">
                <div class="feature-icon"><x-icon name="book-open" size="22px" /></div>
                <h3>Pembelajaran Bermakna</h3>
                <p>Kurikulum Merdeka dengan projek lintas disiplin, pembelajaran berbasis masalah, dan asesmen yang berpusat pada siswa.</p>
            </article>
            <article class="feature-card">
                <div class="feature-icon"><x-icon name="users" size="22px" /></div>
                <h3>Guru Berpengalaman</h3>
                <p>Tenaga pendidik profesional yang terus mengembangkan diri melalui pelatihan, riset kelas, dan komunitas belajar.</p>
            </article>
            <article class="feature-card">
                <div class="feature-icon"><x-icon name="shield" size="22px" /></div>
                <h3>Lingkungan Aman &amp; Inklusif</h3>
                <p>Sekolah ramah anak, bebas perundungan, dengan pembinaan karakter dan layanan bimbingan konseling yang responsif.</p>
            </article>
        </div>
    </div>
</section>

<section class="section">
    <div class="container welcome">
        <div class="principal-card">
            @if($principalPhoto = \App\Support\Media::url($school['principal_photo_path'] ?? null))
                <img src="{{ $principalPhoto }}" alt="Foto {{ $school['principal_name'] ?? 'Kepala Sekolah' }}" class="principal-avatar principal-photo">
            @else
                <div class="principal-avatar">{{ strtoupper(substr($school['principal_name'] ?? 'K', 0, 1)) }}</div>
            @endif
            <h3>{{ $school['principal_name'] ?? 'Kepala Sekolah' }}</h3>
            <div class="role">Kepala Sekolah</div>
            <p class="principal-quote">{{ $school['principal_welcome'] ?? '' }}</p>
        </div>
        <div>
            <span class="eyebrow">Sambutan Kepala Sekolah</span>
            <h2>Bersama membangun masa depan siswa</h2>
            <p class="muted">Kami percaya setiap anak memiliki potensi unik. Tugas sekolah adalah menyediakan ruang aman, bimbingan yang hangat, dan tantangan yang tepat agar potensi tersebut tumbuh optimal.</p>
            <div class="grid grid-2" style="margin-top:1.6rem">
                <div class="feature-card" style="padding:1.1rem 1.2rem">
                    <h3 style="font-size:.95rem">Visi</h3>
                    <p style="font-size:.84rem">{{ $school['vision'] ?? '' }}</p>
                </div>
                <div class="feature-card" style="padding:1.1rem 1.2rem">
                    <h3 style="font-size:.95rem">Akreditasi</h3>
                    <p style="font-size:.84rem">Terakreditasi {{ $school['accreditation'] ?? 'A' }} (NPSN {{ $school['npsn'] ?? '-' }}), berdiri sejak {{ $school['founded_year'] ?? '-' }}.</p>
                </div>
            </div>
            <a href="{{ route('profile') }}" class="btn btn-outline mt-2">Selengkapnya tentang sekolah <x-icon name="arrow-right" size="15px" /></a>
        </div>
    </div>
</section>

<section class="section alt">
    <div class="container">
        <div class="section-head split">
            <div>
                <span class="eyebrow">Berita Sekolah</span>
                <h2>Kabar terbaru dari Nusantara</h2>
                <p>Kegiatan, prestasi, dan inovasi pembelajaran yang kami bagikan untuk orang tua dan masyarakat.</p>
            </div>
            <a href="{{ route('news.index') }}" class="btn btn-outline">Semua berita <x-icon name="arrow-right" size="15px" /></a>
        </div>

        <div class="grid grid-3">
            @foreach($news as $item)
                @include('site.partials.news-card', ['item' => $item])
            @endforeach
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="section-head split">
            <div>
                <span class="eyebrow">Pengumuman</span>
                <h2>Informasi penting untuk warga sekolah</h2>
            </div>
            <a href="{{ route('news.index', ['kategori' => 'pengumuman']) }}" class="btn btn-ghost">Lihat semua <x-icon name="arrow-right" size="15px" /></a>
        </div>

        <div class="notice-list">
            @forelse($announcements as $item)
                <a class="notice-item" href="{{ route('news.show', $item) }}">
                    <div class="notice-date">
                        <strong>{{ $item->published_at?->format('d') }}</strong>
                        <span>{{ $item->published_at?->locale('id')->translatedFormat('M Y') }}</span>
                    </div>
                    <div>
                        <h4>{{ $item->title }}</h4>
                        <p>{{ $item->excerpt }}</p>
                    </div>
                    <x-icon name="chevron-right" size="16px" style="margin-left:auto;color:#94a3b8" />
                </a>
            @empty
                <div class="empty-state">Belum ada pengumuman terbaru.</div>
            @endforelse
        </div>
    </div>
</section>

@if($youtubeEmbed)
<section class="section video-section">
    <div class="container">
        <div class="section-head split">
            <div>
                <span class="eyebrow">Galeri Video</span>
                <h2>Kenali sekolah kami lebih dekat</h2>
                <p>Simak dokumentasi kegiatan, fasilitas, dan suasana belajar di {{ $school['school_short'] ?? 'sekolah' }}.</p>
            </div>
            <a href="{{ $school['youtube_url'] ?? '#' }}" target="_blank" rel="noopener" class="btn btn-outline">
                Buka di YouTube <x-icon name="arrow-right" size="15px" />
            </a>
        </div>
        <div class="video-frame">
            <iframe src="{{ $youtubeEmbed }}"
                title="Video profil {{ $school['school_name'] ?? 'sekolah' }}"
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                allowfullscreen
                loading="lazy"></iframe>
        </div>
    </div>
</section>
@endif

<section class="section alt">
    <div class="container">
        <div class="panel cta-panel" style="padding:2.2rem;display:flex;align-items:center;justify-content:space-between;gap:2rem;flex-wrap:wrap;border:0">
            <div style="max-width:38rem">
                <h2 style="color:#fff;margin-bottom:.4rem">Akses layanan sekolah dalam satu genggaman</h2>
                <p style="color:#b6c6d6;margin:0">Pantau jadwal pelajaran, nilai, kehadiran, dan pengumuman langsung dari ponsel. Aplikasi mobile tersedia untuk siswa dan orang tua.</p>
            </div>
            <div style="display:flex;gap:.6rem;flex-wrap:wrap">
                <a href="{{ route('contact') }}" class="btn btn-accent">Hubungi Kami</a>
                <a href="{{ route('login') }}" class="btn btn-outline" style="background:transparent;color:#fff;border-color:rgba(255,255,255,.35)">Portal Akademik</a>
            </div>
        </div>
    </div>
</section>
@endsection
