@extends('layouts.site')

@section('title', 'Info PTN / Alumni')
@section('description', 'Data sebaran lulusan yang diterima di Perguruan Tinggi Negeri dan universitas ternama.')

@section('content')
<section class="page-hero">
    <div class="container">
        <div class="breadcrumb">
            <a href="{{ route('home') }}">Beranda</a> <span>/</span> <span>Akademik</span> <span>/</span> <span>Info PTN / Alumni</span>
        </div>
        <h1>Info PTN / Alumni</h1>
        <p>Data sebaran lulusan yang diterima di Perguruan Tinggi Negeri dan universitas ternama.</p>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="alumni-stats">
            <div class="alumni-stat">
                <strong>{{ number_format($total, 0, ',', '.') }}</strong>
                <span>Alumni terdata</span>
            </div>
            <div class="alumni-stat">
                <strong>{{ number_format($ptnCount, 0, ',', '.') }}</strong>
                <span>Diterima di PTN</span>
            </div>
            <div class="alumni-stat">
                <strong>{{ number_format($universities->count(), 0, ',', '.') }}</strong>
                <span>Perguruan tinggi</span>
            </div>
            <div class="alumni-stat">
                <strong>{{ $latestYear ?? '-' }}</strong>
                <span>Tahun lulus terbaru</span>
            </div>
        </div>

        @if($items->isEmpty())
            <div class="panel">
                <div class="empty-state">Belum ada data alumni yang dipublikasikan.</div>
            </div>
        @else
            <div class="panel" style="margin-bottom:2rem">
                <div class="panel-head"><h2>Sebaran Perguruan Tinggi</h2></div>
                <div class="panel-body">
                    <div class="alumni-universities">
                        @foreach($universities as $university => $count)
                            <div class="alumni-university">
                                <span>{{ $university }}</span>
                                <strong>{{ $count }} alumni</strong>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="section-head">
                <span class="eyebrow">Daftar Alumni</span>
                <h2>Lulusan diterima di perguruan tinggi</h2>
            </div>

            <div class="grid grid-3">
                @foreach($items as $item)
                    <article class="feature-card">
                        @if($item->photoUrl())
                            <div class="alumni-photo"><img src="{{ $item->photoUrl() }}" alt="Foto {{ $item->name }}" loading="lazy"></div>
                        @else
                            <div class="alumni-photo alumni-photo-empty" aria-hidden="true"><x-icon name="graduation-cap" size="26px" /></div>
                        @endif
                        <span class="badge {{ $item->type === 'ptn' ? 'badge-success' : 'badge-info' }}">{{ $item->typeLabel() }}</span>
                        <h3 style="font-size:1rem;margin-top:.7rem">{{ $item->name }}</h3>
                        <p style="font-size:.88rem;margin-bottom:.3rem"><strong>{{ $item->university }}</strong></p>
                        <p>{{ $item->major ?? 'Program studi belum diisi' }}</p>
                        <p class="muted" style="font-size:.78rem;margin-top:.3rem">Lulus tahun {{ $item->graduation_year }}</p>
                    </article>
                @endforeach
            </div>
        @endif
    </div>
</section>
@endsection
