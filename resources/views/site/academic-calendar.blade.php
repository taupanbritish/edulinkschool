@extends('layouts.site')

@section('title', 'Kalender Akademik')
@section('description', 'Jadwal kegiatan belajar, ujian, libur semester, dan agenda tahunan sekolah.')

@section('content')
<section class="page-hero">
    <div class="container">
        <div class="breadcrumb">
            <a href="{{ route('home') }}">Beranda</a> <span>/</span> <span>Akademik</span> <span>/</span> <span>Kalender Akademik</span>
        </div>
        <h1>Kalender Akademik</h1>
        <p>Jadwal kegiatan belajar mengajar, ujian, libur semester, dan agenda tahunan sekolah.</p>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="filter-bar" style="margin-bottom:1.8rem">
            @foreach($categories as $value => $label)
                <span class="badge {{ ['kegiatan' => 'badge-info', 'ujian' => 'badge-announcement', 'libur' => 'badge-neutral', 'agenda' => 'badge-news'][$value] ?? 'badge-neutral' }}">{{ $label }}</span>
            @endforeach
            <span class="muted" style="font-size:.8rem">{{ $total }} agenda</span>
        </div>

        @forelse($eventsByMonth as $month => $events)
            <section class="calendar-month">
                <div class="calendar-month-head">
                    <h2>{{ $month }}</h2>
                    <span>{{ $events->count() }} agenda</span>
                </div>
                <div class="timeline">
                    @foreach($events as $event)
                        <article class="timeline-item">
                            <div class="timeline-date">
                                <strong>{{ $event->starts_at->format('d') }}</strong>
                                <span>{{ $event->starts_at->locale('id')->translatedFormat('M') }}</span>
                            </div>
                            <div class="timeline-body">
                                <span class="badge {{ ['kegiatan' => 'badge-info', 'ujian' => 'badge-announcement', 'libur' => 'badge-neutral', 'agenda' => 'badge-news'][$event->category] ?? 'badge-neutral' }}">{{ $event->categoryLabel() }}</span>
                                <h3>{{ $event->title }}</h3>
                                <p>{{ $event->dateRange() }}</p>
                                @if($event->description)
                                    <p>{{ $event->description }}</p>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>
        @empty
            <div class="panel">
                <div class="empty-state">Belum ada agenda akademik yang dipublikasikan.</div>
            </div>
        @endforelse
    </div>
</section>
@endsection
