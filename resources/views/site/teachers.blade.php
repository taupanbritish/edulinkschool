@extends('layouts.site')

@section('title', 'Guru')

@section('description', 'Daftar tenaga pendidik dan wali kelas sekolah.')

@section('content')
<section class="page-hero">
    <div class="container">
        <div class="breadcrumb">
            <a href="{{ route('home') }}">Beranda</a> <span>/</span>
            <a href="{{ route('profile') }}">Profil Sekolah</a> <span>/</span> <span>Guru</span>
        </div>
        <h1>Guru</h1>
        <p>Tim pendidik yang mendampingi siswa di kelas, wali kelas, dan kegiatan ekstrakurikuler.</p>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="section-head">
            <span class="eyebrow">Tenaga Pendidik</span>
            <h2>Guru &amp; wali kelas</h2>
            <p>Seluruh guru yang mengajar di sekolah, lengkap dengan wali kelas yang diampu.</p>
        </div>

        @if($teachers->isEmpty())
            <div class="empty-state">Belum ada data guru.</div>
        @else
            <div class="grid grid-3">
                @foreach($teachers as $teacher)
                    @include('site.partials.teacher-card')
                @endforeach
            </div>
            @include('partials.pagination', ['paginator' => $teachers])
        @endif
    </div>
</section>
@endsection
