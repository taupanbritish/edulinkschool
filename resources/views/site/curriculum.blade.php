@extends('layouts.site')

@section('title', 'Kurikulum')
@section('description', 'Sistem kurikulum dan pilihan kelompok mata pelajaran di sekolah.')

@section('content')
<section class="page-hero">
    <div class="container">
        <div class="breadcrumb">
            <a href="{{ route('home') }}">Beranda</a> <span>/</span> <span>Akademik</span> <span>/</span> <span>Kurikulum</span>
        </div>
        <h1>Kurikulum</h1>
        <p>Sistem kurikulum yang diterapkan beserta pilihan kelompok mata pelajaran dan peminatan.</p>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="panel" style="padding:2rem 2.2rem;margin-bottom:2rem">
            <span class="eyebrow">Kurikulum yang diterapkan</span>
            <h2>{{ $curriculumName }}</h2>
            @if($curriculumDescription)
                <p class="muted" style="white-space:pre-line;max-width:56rem">{{ $curriculumDescription }}</p>
            @else
                <p class="muted">Penjelasan kurikulum belum diisi oleh administrator sekolah.</p>
            @endif
        </div>

        <div class="section-head">
            <span class="eyebrow">Kelompok Mata Pelajaran</span>
            <h2>Pilihan kelompok dan peminatan</h2>
            <p>Kelompok mata pelajaran yang tersedia bagi siswa sesuai minat dan rencana studi lanjut.</p>
        </div>

        @if($groups->isEmpty())
            <div class="panel">
                <div class="empty-state">Belum ada kelompok mata pelajaran yang dipublikasikan.</div>
            </div>
        @else
            <div class="grid grid-2">
                @foreach($groups as $group)
                    <article class="feature-card">
                        <div class="feature-icon"><x-icon name="book-open" size="22px" /></div>
                        <h3>{{ $group->name }}</h3>
                        @if($group->description)
                            <p>{{ $group->description }}</p>
                        @endif
                        @if(count($group->subjectList()))
                            <div class="subject-chips">
                                @foreach($group->subjectList() as $subject)
                                    <span class="subject-chip">{{ $subject }}</span>
                                @endforeach
                            </div>
                        @endif
                    </article>
                @endforeach
            </div>
        @endif
    </div>
</section>
@endsection
