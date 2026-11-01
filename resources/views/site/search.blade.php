@extends('layouts.site')

@section('title', $term !== '' ? 'Pencarian: '.$term : 'Pencarian')

@section('content')
@php
    $quickLinks = [
        ['label' => 'Berita & Pengumuman', 'url' => route('news.index'), 'icon' => 'megaphone'],
        ['label' => 'Majalah / Artikel Siswa', 'url' => route('writings.index'), 'icon' => 'book-open'],
        ['label' => 'Guru', 'url' => route('teachers'), 'icon' => 'users'],
        ['label' => 'Fasilitas', 'url' => route('facilities'), 'icon' => 'school'],
        ['label' => 'Ekstrakurikuler', 'url' => route('extracurriculars'), 'icon' => 'award'],
        ['label' => 'Prestasi Akademik', 'url' => route('achievements.academic'), 'icon' => 'star'],
        ['label' => 'Bimbingan Konseling', 'url' => route('counseling'), 'icon' => 'message'],
        ['label' => 'Kontak', 'url' => route('contact'), 'icon' => 'phone'],
    ];
    $tooShort = mb_strlen($term) < 2;
@endphp

<section class="page-hero">
    <div class="container">
        <div class="breadcrumb">
            <a href="{{ route('home') }}">Beranda</a> <span>/</span> <span>Pencarian</span>
        </div>
        <h1>Pencarian Cepat</h1>
        <p>Temukan berita, artikel siswa, profil guru, fasilitas, dan informasi sekolah lainnya.</p>
    </div>
</section>

<section class="section">
    <div class="container">
        <form class="filter-bar" method="GET" action="{{ route('search') }}" style="margin-bottom:1.6rem">
            <input type="search" name="q" value="{{ $term }}" class="input" placeholder="Ketik kata kunci pencarian…" style="min-width:280px;flex:1" autofocus>
            <button type="submit" class="btn btn-primary btn-sm"><x-icon name="search" size="15px" /> Cari</button>
        </form>

        @if($tooShort)
            <div class="panel">
                <div class="panel-body">
                    <p class="muted" style="margin-top:0">Masukkan minimal dua karakter untuk mulai mencari. Anda juga dapat menjelajahi halaman berikut:</p>
                    <div class="social-links">
                        @foreach($quickLinks as $link)
                            <a class="social-link" href="{{ $link['url'] }}">
                                <span class="social-icon"><x-icon :name="$link['icon']" size="18px" /></span>
                                <span><strong>{{ $link['label'] }}</strong><small>Buka halaman</small></span>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        @else
            <div class="search-summary">
                <span class="muted">{{ $total }} hasil untuk <strong>“{{ $term }}”</strong></span>
                <a href="{{ route('search') }}" class="btn btn-ghost btn-sm"><x-icon name="x" size="13px" /> Hapus pencarian</a>
            </div>

            @if($total === 0)
                <div class="panel">
                    <div class="empty-state">
                        Tidak ada hasil yang cocok dengan “{{ $term }}”. Coba kata kunci lain atau jelajahi halaman berikut.
                    </div>
                    <div class="panel-body">
                        <div class="social-links">
                            @foreach(array_slice($quickLinks, 0, 4) as $link)
                                <a class="social-link" href="{{ $link['url'] }}">
                                    <span class="social-icon"><x-icon :name="$link['icon']" size="18px" /></span>
                                    <span><strong>{{ $link['label'] }}</strong><small>Buka halaman</small></span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>
            @else
                @foreach($groups as $group)
                    <section class="search-group">
                        <h2><x-icon :name="$group['icon']" size="18px" /> {{ $group['label'] }} <span class="muted" style="font-size:.8rem;font-weight:400">{{ count($group['items']) }} hasil</span></h2>
                        <div class="search-results">
                            @foreach($group['items'] as $item)
                                <a class="search-result" href="{{ $item['url'] }}">
                                    <strong>{{ $item['title'] }}</strong>
                                    @if(filled($item['meta']))
                                        <span>{{ $item['meta'] }}</span>
                                    @endif
                                    @if(filled($item['excerpt']))
                                        <p>{{ $item['excerpt'] }}</p>
                                    @endif
                                </a>
                            @endforeach
                        </div>
                    </section>
                @endforeach
            @endif
        @endif
    </div>
</section>
@endsection
