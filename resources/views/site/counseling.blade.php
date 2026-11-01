@extends('layouts.site')

@section('title', 'Bimbingan Konseling')
@section('description', 'Layanan konsultasi siswa dan program pengembangan karakter di sekolah.')

@section('content')
<section class="page-hero">
    <div class="container">
        <div class="breadcrumb">
            <a href="{{ route('home') }}">Beranda</a> <span>/</span> <span>Kesiswaan &amp; Ekstrakurikuler</span> <span>/</span> <span>Bimbingan Konseling</span>
        </div>
        <h1>Bimbingan Konseling</h1>
        <p>Layanan konsultasi siswa dan program pengembangan karakter yang aman, rahasia, dan berpihak pada siswa.</p>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="panel" style="padding:2rem 2.2rem;margin-bottom:2rem;display:flex;gap:1.2rem;align-items:flex-start;flex-wrap:wrap">
            <div class="feature-icon" style="flex:none"><x-icon name="message" size="22px" /></div>
            <div style="flex:1;min-width:260px">
                <span class="eyebrow">Ruang Aman Siswa</span>
                <h2 style="margin:.2rem 0 .5rem">Konsultasi &amp; pendampingan karakter</h2>
                <p class="muted" style="margin:0">Guru Bimbingan Konseling mendampingi siswa menghadapi persoalan pribadi, akademik, sosial, dan karier. Setiap konsultasi dijaga kerahasiaannya dan diarahkan pada solusi yang sehat.</p>
            </div>
        </div>

        <div class="section-head">
            <span class="eyebrow">Layanan</span>
            <h2>Layanan konsultasi siswa</h2>
            <p>Pilih layanan yang sesuai kebutuhan, lalu hubungi guru BK untuk membuat janji konsultasi.</p>
        </div>

        @if($services->isEmpty())
            <div class="panel">
                <div class="empty-state">Belum ada layanan konsultasi yang dipublikasikan.</div>
            </div>
        @else
            <div class="grid grid-2">
                @foreach($services as $service)
                    <article class="feature-card service-card">
                        <div class="feature-icon"><x-icon name="message" size="20px" /></div>
                        <h3>{{ $service->title }}</h3>
                        @if($service->description)
                            <p>{{ $service->description }}</p>
                        @endif
                        <div class="service-meta">
                            @if($service->schedule)
                                <span><x-icon name="clock" size="13px" /> {{ $service->schedule }}</span>
                            @endif
                            @if($service->contact)
                                <span><x-icon name="map-pin" size="13px" /> {{ $service->contact }}</span>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </div>
</section>

<section class="section alt">
    <div class="container">
        <div class="section-head">
            <span class="eyebrow">Pengembangan Karakter</span>
            <h2>Program pembinaan siswa</h2>
            <p>Program rutin yang menumbuhkan integritas, empati, dan keterampilan sosial siswa.</p>
        </div>

        @if($programs->isEmpty())
            <div class="panel">
                <div class="empty-state">Belum ada program pengembangan karakter yang dipublikasikan.</div>
            </div>
        @else
            <div class="grid grid-2">
                @foreach($programs as $program)
                    <article class="feature-card service-card">
                        <div class="feature-icon"><x-icon name="shield" size="20px" /></div>
                        <h3>{{ $program->title }}</h3>
                        @if($program->description)
                            <p>{{ $program->description }}</p>
                        @endif
                        <div class="service-meta">
                            @if($program->schedule)
                                <span><x-icon name="calendar" size="13px" /> {{ $program->schedule }}</span>
                            @endif
                            @if($program->contact)
                                <span><x-icon name="phone" size="13px" /> {{ $program->contact }}</span>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>
        @endif

        <div class="panel" style="margin-top:2rem;padding:1.6rem 1.8rem">
            <h3 style="margin-bottom:.4rem">Ingin berkonsultasi?</h3>
            <p class="muted" style="margin:0">Datang langsung ke ruang BK pada jam layanan, atau hubungi {{ $school['phone'] ?? 'sekolah' }} / {{ $school['email'] ?? 'email sekolah' }} untuk membuat janji.</p>
        </div>
    </div>
</section>
@endsection
