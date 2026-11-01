@extends('layouts.site')

@section('title', 'Profil Sekolah')

@section('content')
<section class="page-hero">
    <div class="container">
        <div class="breadcrumb">
            <a href="{{ route('home') }}">Beranda</a> <span>/</span> <span>Profil Sekolah</span>
        </div>
        <h1>Profil {{ $school['school_name'] ?? 'Sekolah' }}</h1>
        <p>Mengenal sejarah, visi, misi, dan tenaga pendidik yang mendampingi siswa setiap hari.</p>
    </div>
</section>

<section class="section">
    <div class="container profile-intro">
        <div>
            <span class="eyebrow">Tentang Kami</span>
            <h2>Empat dekade mendidik generasi Nusantara</h2>
            <p class="muted">{{ $school['history'] ?? '' }}</p>
            <p class="muted">{{ $school['principal_welcome'] ?? '' }}</p>

            <div class="grid grid-3" style="margin-top:1.8rem">
                <div class="feature-card" style="text-align:center">
                    <div class="value" style="font-size:1.6rem;font-weight:800;color:var(--navy-900)">{{ $school['founded_year'] ?? '-' }}</div>
                    <p style="margin:0;font-size:.8rem">Tahun berdiri</p>
                </div>
                <div class="feature-card" style="text-align:center">
                    <div style="font-size:1.6rem;font-weight:800;color:var(--navy-900)">{{ $school['accreditation'] ?? 'A' }}</div>
                    <p style="margin:0;font-size:.8rem">Akreditasi</p>
                </div>
                <div class="feature-card" style="text-align:center">
                    <div style="font-size:1.6rem;font-weight:800;color:var(--navy-900)">{{ $school['npsn'] ?? '-' }}</div>
                    <p style="margin:0;font-size:.8rem">NPSN</p>
                </div>
            </div>
        </div>

        <aside class="info-card">
            <h3><x-icon name="school" size="18px" /> Identitas Sekolah</h3>
            <div class="info-list">
                <div><span>Nama sekolah</span><span>{{ $school['school_name'] ?? '-' }}</span></div>
                <div><span>NPSN</span><span>{{ $school['npsn'] ?? '-' }}</span></div>
                <div><span>Akreditasi</span><span>{{ $school['accreditation'] ?? '-' }}</span></div>
                <div><span>Kepala sekolah</span><span>{{ $school['principal_name'] ?? '-' }}</span></div>
                <div><span>Alamat</span><span>{{ $school['address'] ?? '-' }}</span></div>
                <div><span>Telepon</span><span>{{ $school['phone'] ?? '-' }}</span></div>
                <div><span>Email</span><span>{{ $school['email'] ?? '-' }}</span></div>
                <div><span>Jam layanan</span><span>{{ $school['office_hours'] ?? '-' }}</span></div>
            </div>
        </aside>
    </div>

    <div class="container" style="margin-top:3.5rem">
        <div class="mv-grid">
            <div class="mv-card">
                <span class="eyebrow">Visi</span>
                <h3>Visi Sekolah</h3>
                <p class="muted mb-0">{{ $school['vision'] ?? '-' }}</p>
            </div>
            <div class="mv-card">
                <span class="eyebrow">Misi</span>
                <h3>Misi Sekolah</h3>
                <ul>
                    @foreach(preg_split('/\r\n|\r|\n/', $school['mission'] ?? '') as $mission)
                        @if(trim($mission) !== '')
                            <li>{{ trim($mission) }}</li>
                        @endif
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
</section>

<section class="section alt" id="guru">
    <div class="container">
        <div class="section-head split">
            <div>
                <span class="eyebrow">Tenaga Pendidik</span>
                <h2>Guru &amp; wali kelas</h2>
                <p>Tim pendidik yang berdedikasi mendampingi siswa di kelas dan kegiatan ekstrakurikuler.</p>
            </div>
            <a href="{{ route('teachers') }}" class="btn btn-outline btn-sm">Lihat semua guru <x-icon name="arrow-right" size="14px" /></a>
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

<section class="section" id="tata-usaha">
    <div class="container">
        <div class="section-head split">
            <div>
                <span class="eyebrow">Layanan Administrasi</span>
                <h2>Tata Usaha</h2>
                <p>Tim administrasi yang mendukung layanan akademik, kesiswaan, dan operasional sekolah.</p>
            </div>
            <a href="{{ route('staff') }}" class="btn btn-outline btn-sm">Lihat semua tata usaha <x-icon name="arrow-right" size="14px" /></a>
        </div>

        @if($staff->isEmpty())
            <div class="empty-state">Belum ada data staf tata usaha.</div>
        @else
            <div class="grid grid-3">
                @foreach($staff as $member)
                    @include('site.partials.staff-card')
                @endforeach
            </div>
            @include('partials.pagination', ['paginator' => $staff])
        @endif
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="grid grid-3">
            <div class="feature-card">
                <div class="feature-icon"><x-icon name="target" size="22px" /></div>
                <h3>Kurikulum Merdeka</h3>
                <p>Pembelajaran berdiferensiasi dengan projek penguatan profil pelajar Pancasila.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon"><x-icon name="award" size="22px" /></div>
                <h3>Prestasi Berkelanjutan</h3>
                <p>Pembinaan olimpiade, riset remaja, seni, dan olahraga sepanjang tahun.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon"><x-icon name="message" size="22px" /></div>
                <h3>Kemitraan Orang Tua</h3>
                <p>Komunikasi terbuka melalui wali kelas, pertemuan rutin, dan aplikasi mobile.</p>
            </div>
        </div>
    </div>
</section>
@endsection
