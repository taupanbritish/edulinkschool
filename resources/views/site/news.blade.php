@extends('layouts.site')

@section('title', 'Berita & Pengumuman')

@section('content')
<section class="page-hero">
    <div class="container">
        <div class="breadcrumb">
            <a href="{{ route('home') }}">Beranda</a> <span>/</span> <span>Berita &amp; Pengumuman</span>
        </div>
        <h1>Berita &amp; Pengumuman</h1>
        <p>Ikuti kabar terbaru seputar kegiatan belajar, prestasi siswa, dan informasi resmi sekolah.</p>
    </div>
</section>

<section class="section">
    <div class="container">
        <form method="GET" action="{{ route('news.index') }}" class="filter-bar" style="margin-bottom:1.6rem;justify-content:space-between">
            <div class="filter-bar">
                <a href="{{ route('news.index') }}" class="btn btn-sm {{ !$category ? 'btn-primary' : 'btn-outline' }}">Semua</a>
                <a href="{{ route('news.index', ['kategori' => 'berita']) }}" class="btn btn-sm {{ $category === 'berita' ? 'btn-primary' : 'btn-outline' }}">Berita</a>
                <a href="{{ route('news.index', ['kategori' => 'pengumuman']) }}" class="btn btn-sm {{ $category === 'pengumuman' ? 'btn-primary' : 'btn-outline' }}">Pengumuman</a>
                <a href="{{ route('writings.index') }}" class="btn btn-sm btn-outline"><x-icon name="book-open" size="14px" /> Majalah / Artikel Siswa</a>
            </div>
            <div class="filter-bar">
                @if($category)
                    <input type="hidden" name="kategori" value="{{ $category }}">
                @endif
                <input type="search" name="q" value="{{ $search }}" class="input" placeholder="Cari judul berita…" style="min-width:240px">
                <button type="submit" class="btn btn-outline btn-sm"><x-icon name="search" size="15px" /> Cari</button>
            </div>
        </form>

        @if($news->isEmpty())
            <div class="panel">
                <div class="empty-state">Tidak ada artikel yang cocok dengan pencarian Anda.</div>
            </div>
        @else
            <div class="grid grid-3">
                @foreach($news as $item)
                    @include('site.partials.news-card', ['item' => $item])
                @endforeach
            </div>

            @include('partials.pagination', ['paginator' => $news])
        @endif
    </div>
</section>
@endsection
