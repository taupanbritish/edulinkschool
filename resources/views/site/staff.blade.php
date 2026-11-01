@extends('layouts.site')

@section('title', 'Tata Usaha')

@section('description', 'Tenaga administrasi sekolah yang melayani kebutuhan akademik siswa, orang tua, dan guru.')

@section('content')
<section class="page-hero">
    <div class="container">
        <div class="breadcrumb">
            <a href="{{ route('home') }}">Beranda</a> <span>/</span> <span>Tata Usaha</span>
        </div>
        <h1>Tata Usaha</h1>
        <p>Tim administrasi yang mendukung layanan akademik, kesiswaan, dan operasional sekolah setiap hari.</p>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="section-head">
            <span class="eyebrow">Layanan Administrasi</span>
            <h2>Staf tata usaha</h2>
            <p>Silakan hubungi bagian tata usaha untuk layanan surat menyurat, legalisir, dan administrasi siswa.</p>
        </div>

        @if($staff->isEmpty())
            <div class="empty-state">Belum ada data staf tata usaha.</div>
        @else
            <div class="grid grid-3">
                @foreach($staff as $member)
                    @include('site.partials.staff-card')
                @endforeach
            </div>
        @endif
    </div>
</section>
@endsection
