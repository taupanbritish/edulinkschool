@extends('layouts.site')

@section('title', 'Struktur Organisasi')

@section('description', 'Susunan organisasi sekolah dari kepala sekolah hingga koordinator bidang.')

@section('content')
<section class="page-hero">
    <div class="container">
        <div class="breadcrumb">
            <a href="{{ route('home') }}">Beranda</a> <span>/</span> <span>Struktur Organisasi</span>
        </div>
        <h1>Struktur Organisasi</h1>
        <p>Susunan kepemimpinan dan koordinasi {{ $school['school_name'] ?? 'sekolah' }}.</p>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="section-head">
            <span class="eyebrow">Kelembagaan</span>
            <h2>Bagan organisasi sekolah</h2>
            <p>{{ $total }} jabatan tercatat, dari pimpinan sekolah hingga koordinator bidang.</p>
        </div>

        @if($roots === [])
            <div class="empty-state">Belum ada data struktur organisasi.</div>
        @else
            <div class="org-chart">
                @foreach($roots as $node)
                    @include('site.partials.org-node', ['node' => $node])
                @endforeach
            </div>
        @endif
    </div>
</section>

<script>
    (function () {
        const chart = document.querySelector('.org-chart');
        if (chart && chart.scrollWidth > chart.clientWidth) {
            chart.scrollLeft = (chart.scrollWidth - chart.clientWidth) / 2;
        }
    })();
</script>
@endsection
