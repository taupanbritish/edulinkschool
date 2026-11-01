@extends('layouts.site')

@section('title', $pageTitle)
@section('description', $pageDescription)

@section('content')
<section class="page-hero">
    <div class="container">
        <div class="breadcrumb">
            <a href="{{ route('home') }}">Beranda</a> <span>/</span> <span>{{ $pageTitle }}</span>
        </div>
        <h1>{{ $pageTitle }}</h1>
        <p>{{ $pageDescription }}</p>
    </div>
</section>

<section class="section">
    <div class="container">
        @if($items->isEmpty())
            <div class="panel">
                <div class="empty-state">{{ $emptyMessage }}</div>
            </div>
        @else
            <div class="showcase-grid">
                @foreach($items as $item)
                    @include('site.partials.showcase-card', ['item' => $item, 'placeholderIcon' => $placeholderIcon])
                @endforeach
            </div>
        @endif
    </div>
</section>
@endsection
