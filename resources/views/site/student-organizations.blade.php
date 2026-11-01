@extends('layouts.site')

@section('title', 'Organisasi Siswa')
@section('description', 'Informasi seputar OSIS dan MPK, visi, misi, serta program kerja organisasi siswa sekolah.')

@section('content')
<section class="page-hero">
    <div class="container">
        <div class="breadcrumb">
            <a href="{{ route('home') }}">Beranda</a> <span>/</span> <span>Kesiswaan &amp; Ekstrakurikuler</span> <span>/</span> <span>Organisasi Siswa</span>
        </div>
        <h1>Organisasi Siswa</h1>
        <p>Informasi seputar OSIS dan MPK sebagai wadah kepemimpinan, aspirasi, dan pelayanan siswa.</p>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="section-head">
            <span class="eyebrow">Kesiswaan</span>
            <h2>OSIS &amp; MPK</h2>
            <p>Dua organisasi siswa yang bekerja bersama untuk menciptakan lingkungan sekolah yang aktif, demokratis, dan peduli.</p>
        </div>

        @if($organizations->isEmpty())
            <div class="panel">
                <div class="empty-state">Belum ada informasi organisasi siswa yang dipublikasikan.</div>
            </div>
        @else
            <div class="stack">
                @foreach($organizations as $organization)
                    <article class="panel student-org-card">
                        <div class="student-org-head">
                            <div class="student-org-media">
                                @if($organization->photoUrl())
                                    <img src="{{ $organization->photoUrl() }}" alt="Foto {{ $organization->name }}" loading="lazy">
                                @else
                                    <div class="student-org-placeholder"><x-icon name="users" size="34px" /></div>
                                @endif
                            </div>
                            <div>
                                @if($organization->short_name)
                                    <span class="badge badge-info">{{ $organization->short_name }}</span>
                                @endif
                                <h2 class="student-org-title">{{ $organization->name }}</h2>
                                @if($organization->description)
                                    <p class="muted">{{ $organization->description }}</p>
                                @endif
                                @if($organization->advisor)
                                    <span class="student-org-meta"><x-icon name="user" size="13px" /> Pembina: {{ $organization->advisor }}</span>
                                @endif
                            </div>
                        </div>

                        <div class="student-org-sections">
                            @if($organization->vision)
                                <section>
                                    <h3>Visi</h3>
                                    <p>{{ $organization->vision }}</p>
                                </section>
                            @endif
                            @if(count($organization->missionList()))
                                <section>
                                    <h3>Misi</h3>
                                    <ul class="student-org-list">
                                        @foreach($organization->missionList() as $mission)
                                            <li>{{ $mission }}</li>
                                        @endforeach
                                    </ul>
                                </section>
                            @endif
                            @if(count($organization->programList()))
                                <section>
                                    <h3>Program Kerja</h3>
                                    <div class="subject-chips">
                                        @foreach($organization->programList() as $program)
                                            <span class="subject-chip">{{ $program }}</span>
                                        @endforeach
                                    </div>
                                </section>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </div>
</section>
@endsection
