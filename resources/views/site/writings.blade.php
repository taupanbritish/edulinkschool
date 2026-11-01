@extends('layouts.site')

@section('title', 'Majalah / Artikel Siswa')

@section('content')
<section class="page-hero">
    <div class="container">
        <div class="breadcrumb">
            <a href="{{ route('home') }}">Beranda</a> <span>/</span>
            <a href="{{ route('news.index') }}">Berita &amp; Pengumuman</a> <span>/</span>
            <span>Majalah / Artikel Siswa</span>
        </div>
        <h1>Majalah / Artikel Siswa</h1>
        <p>Ruang literasi untuk karya tulis, cerpen, dan opini siswa serta guru.</p>
    </div>
</section>

<section class="section">
    <div class="container">
        <form method="GET" action="{{ route('writings.index') }}" class="writings-toolbar">
            <div class="filter-bar">
                <a href="{{ route('writings.index') }}" class="btn btn-sm {{ !$category ? 'btn-primary' : 'btn-outline' }}">Semua</a>
                @foreach(\App\Models\StudentWriting::CATEGORIES as $value => $label)
                    <a href="{{ route('writings.index', ['kategori' => $value]) }}" class="btn btn-sm {{ $category === $value ? 'btn-primary' : 'btn-outline' }}">{{ $label }}</a>
                @endforeach
            </div>
            <div class="filter-bar">
                @if($category)
                    <input type="hidden" name="kategori" value="{{ $category }}">
                @endif
                <input type="search" name="q" value="{{ $search }}" class="input" placeholder="Cari judul atau penulis…" style="min-width:240px">
                <button type="submit" class="btn btn-outline btn-sm"><x-icon name="search" size="15px" /> Cari</button>
            </div>
        </form>

        @if($writings->isEmpty())
            <div class="panel">
                <div class="empty-state">Belum ada karya yang cocok dengan pencarian Anda.</div>
            </div>
        @else
            <div class="grid grid-3">
                @foreach($writings as $writing)
                    <article class="news-card">
                        <a href="{{ route('writings.show', $writing) }}" class="news-cover {{ $writing->photoUrl() ? 'has-photo' : '' }}" style="background:linear-gradient(135deg,var(--teal-600),var(--navy-900))">
                            @if($writing->photoUrl())
                                <img src="{{ $writing->photoUrl() }}" alt="" class="cover-photo" loading="lazy">
                            @endif
                            <span class="cover-mark">{{ $school['school_short'] ?? 'N1' }}</span>
                            <span class="cover-label">{{ $writing->categoryLabel() }} · {{ $writing->published_at?->locale('id')->translatedFormat('d M Y') }}</span>
                        </a>
                        <div class="news-body">
                            <div class="news-meta">
                                <span class="badge badge-news">{{ $writing->categoryLabel() }}</span>
                                <span>{{ $writing->author_name }}</span>
                            </div>
                            <h3><a href="{{ route('writings.show', $writing) }}">{{ $writing->title }}</a></h3>
                            <p>{{ $writing->excerpt }}</p>
                            <a href="{{ route('writings.show', $writing) }}" class="read-more">
                                Baca selengkapnya <x-icon name="arrow-right" size="14px" />
                            </a>
                        </div>
                    </article>
                @endforeach
            </div>

            @include('partials.pagination', ['paginator' => $writings])
        @endif
    </div>
</section>
@endsection
