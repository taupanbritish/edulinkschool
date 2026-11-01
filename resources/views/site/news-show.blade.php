@extends('layouts.site')

@section('title', $article->title)
@section('description', $article->excerpt)

@section('content')
<section class="page-hero">
    <div class="container">
        <div class="breadcrumb">
            <a href="{{ route('home') }}">Beranda</a> <span>/</span>
            <a href="/berita">Berita</a> <span>/</span>
            <span>{{ $article->categoryLabel() }}</span>
        </div>
        <h1>{{ $article->title }}</h1>
        <p>{{ $article->published_at?->locale('id')->translatedFormat('l, d F Y') }} · {{ $article->author?->name ?? 'Humas Sekolah' }}</p>
    </div>
</section>

<section class="section">
    <div class="container article-layout">
        <article class="article">
            <div class="article-header">
                <span class="badge {{ $article->category === 'pengumuman' ? 'badge-announcement' : 'badge-news' }}">{{ $article->categoryLabel() }}</span>
                <h1>{{ $article->title }}</h1>
                <p class="muted mb-0">{{ $article->excerpt }}</p>
            </div>
            <div class="article-body">
                {!! nl2br(e($article->body)) !!}
            </div>

            @if($article->attachments->isNotEmpty())
                @php($photos = $article->attachments->where('type', 'foto'))
                @php($videos = $article->attachments->where('type', 'video'))
                <section class="article-gallery">
                    <div class="gallery-head">
                        <h2>Galeri</h2>
                        <span>{{ $photos->count() }} foto · {{ $videos->count() }} video</span>
                    </div>

                    @if($photos->isNotEmpty())
                        <div class="gallery-grid">
                            @foreach($photos as $photo)
                                <a href="{{ $photo->mediaUrl() }}" target="_blank" rel="noopener" class="gallery-item">
                                    <img src="{{ $photo->mediaUrl() }}" alt="{{ $photo->caption ?: $article->title }}" loading="lazy">
                                    @if($photo->caption)
                                        <span class="gallery-caption">{{ $photo->caption }}</span>
                                    @endif
                                </a>
                            @endforeach
                        </div>
                    @endif

                    @if($videos->isNotEmpty())
                        <div class="gallery-videos">
                            @foreach($videos as $video)
                                <figure class="gallery-video">
                                    @if($video->embedUrl())
                                        <div class="video-frame">
                                            <iframe src="{{ $video->embedUrl() }}" title="{{ $video->caption ?: 'Video '.$article->title }}" loading="lazy" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                                        </div>
                                    @else
                                        <video controls preload="metadata" src="{{ $video->mediaUrl() }}"></video>
                                    @endif
                                    @if($video->caption)
                                        <figcaption>{{ $video->caption }}</figcaption>
                                    @endif
                                </figure>
                            @endforeach
                        </div>
                    @endif
                </section>
            @endif

            <div class="mt-3">
                <a href="{{ route('news.index') }}" class="btn btn-outline btn-sm"><x-icon name="arrow-left" size="14px" /> Kembali ke daftar</a>
            </div>
        </article>

        <aside class="stack">
            <div class="sidebar-card">
                <h3>Artikel Terkait</h3>
                <div class="sidebar-list">
                    @forelse($related as $item)
                        <a href="{{ route('news.show', $item) }}">
                            {{ $item->title }}
                            <span>{{ $item->published_at?->locale('id')->translatedFormat('d M Y') }}</span>
                        </a>
                    @empty
                        <span class="muted">Belum ada artikel terkait.</span>
                    @endforelse
                </div>
            </div>

            <div class="sidebar-card">
                <h3>Butuh informasi?</h3>
                <p class="muted" style="font-size:.85rem">Hubungi tata usaha sekolah pada hari kerja pukul 07.00 – 15.00 WIB.</p>
                <a href="{{ route('contact') }}" class="btn btn-primary btn-sm btn-block">Hubungi Sekolah</a>
            </div>
        </aside>
    </div>
</section>
@endsection
