@extends('layouts.site')

@section('title', $writing->title)
@section('description', $writing->excerpt)

@section('content')
<section class="page-hero">
    <div class="container">
        <div class="breadcrumb">
            <a href="{{ route('home') }}">Beranda</a> <span>/</span>
            <a href="{{ route('writings.index') }}">Majalah / Artikel Siswa</a> <span>/</span>
            <span>{{ $writing->categoryLabel() }}</span>
        </div>
        <h1>{{ $writing->title }}</h1>
        <p>{{ $writing->published_at?->locale('id')->translatedFormat('l, d F Y') }} · {{ $writing->author_name }} ({{ $writing->authorTypeLabel() }})</p>
    </div>
</section>

<section class="section">
    <div class="container article-layout">
        <article class="article">
            <div class="article-header">
                <span class="badge badge-news">{{ $writing->categoryLabel() }}</span>
                <h1>{{ $writing->title }}</h1>
                <p class="muted mb-0">{{ $writing->excerpt }}</p>
            </div>

            @if($writing->photoUrl())
                <img src="{{ $writing->photoUrl() }}" alt="{{ $writing->title }}" class="article-cover" loading="lazy">
            @endif

            <div class="article-body">
                {!! nl2br(e($writing->body)) !!}
            </div>

            <div class="mt-3">
                <a href="{{ route('writings.index') }}" class="btn btn-outline btn-sm"><x-icon name="arrow-left" size="14px" /> Kembali ke Majalah / Artikel Siswa</a>
            </div>
        </article>

        <aside class="stack">
            <div class="sidebar-card">
                <h3>Karya Lainnya</h3>
                <div class="sidebar-list">
                    @forelse($related as $item)
                        <a href="{{ route('writings.show', $item) }}">
                            {{ $item->title }}
                            <span>{{ $item->author_name }} · {{ $item->published_at?->locale('id')->translatedFormat('d M Y') }}</span>
                        </a>
                    @empty
                        <span class="muted">Belum ada karya lain pada kategori ini.</span>
                    @endforelse
                </div>
            </div>

            <div class="sidebar-card">
                <h3>Ruang Literasi</h3>
                <p class="muted" style="font-size:.86rem">Majalah / Artikel Siswa adalah ruang literasi sekolah untuk karya tulis, cerpen, dan opini siswa serta guru.</p>
                <a href="{{ route('contact') }}#kirim-pesan" class="btn btn-outline btn-sm">Kirim Saran</a>
            </div>
        </aside>
    </div>
</section>
@endsection
