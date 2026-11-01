@extends('layouts.site')

@section('title', $pageTitle)
@section('description', $pageDescription)

@section('content')
<section class="page-hero">
    <div class="container">
        <div class="breadcrumb">
            <a href="{{ route('home') }}">Beranda</a> <span>/</span> <span>Prestasi</span> <span>/</span> <span>{{ $pageTitle }}</span>
        </div>
        <h1>{{ $pageTitle }}</h1>
        <p>{{ $pageDescription }}</p>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="filter-bar" style="margin-bottom:1.6rem">
            <a href="{{ route('achievements.academic') }}" class="btn btn-sm {{ $category === 'akademik' ? 'btn-primary' : 'btn-outline' }}">Prestasi Akademik</a>
            <a href="{{ route('achievements.non_academic') }}" class="btn btn-sm {{ $category === 'non_akademik' ? 'btn-primary' : 'btn-outline' }}">Prestasi Non Akademik</a>
        </div>

        @if($items->isEmpty())
            <div class="panel">
                <div class="empty-state">Belum ada data {{ strtolower($pageTitle) }} yang dipublikasikan.</div>
            </div>
        @else
            <div class="grid grid-3">
                @foreach($items as $item)
                    <article class="showcase-card">
                        <div class="showcase-media">
                            @if($item->photoUrl())
                                <img src="{{ $item->photoUrl() }}" alt="{{ $item->title }}" loading="lazy">
                            @else
                                <div class="showcase-placeholder"><x-icon name="award" size="30px" /></div>
                            @endif
                        </div>
                        <div class="showcase-body">
                            @if($item->level)
                                <span class="badge badge-info" style="align-self:flex-start">{{ $item->level }}</span>
                            @endif
                            <h2>{{ $item->title }}</h2>
                            @if($item->description)
                                <p>{{ $item->description }}</p>
                            @endif
                            <div class="achievement-meta">
                                @if($item->recipient)
                                    <span><x-icon name="user" size="13px" /> {{ $item->recipient }}</span>
                                @endif
                                @if($item->achieved_at)
                                    <span><x-icon name="calendar" size="13px" /> {{ $item->achieved_at->locale('id')->translatedFormat('d F Y') }}</span>
                                @endif
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </div>
</section>
@endsection
