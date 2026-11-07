<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Panel') — {{ $school['school_short'] ?? 'Sekolah' }}</title>
    <x-favicon />
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body class="admin-body {{ auth()->user()->isReadOnly() ? 'panel-readonly' : '' }}">
<div class="admin-shell">
    <aside class="sidebar">
        <div class="sidebar-brand">
            <x-brand-mark />
            <div>
                <strong>{{ $school['school_short'] ?? 'Sekolah' }}</strong>
                <span>
                    @if(auth()->user()->isReadOnly())
                        Panel Kepala Sekolah
                    @elseif(auth()->user()->isWakasekKurikulum())
                        Panel Wakasek Kurikulum
                    @elseif(auth()->user()->isPanelUser())
                        Panel Admin
                    @else
                        Ruang Guru
                    @endif
                </span>
            </div>
        </div>

        <nav class="sidebar-nav">
            @if(auth()->user()->isPanelUser())
                <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <x-icon name="dashboard" /> Dashboard
                </a>
                @if(auth()->user()->isWakasekKurikulum())
                    <div class="sidebar-label">Akademik</div>
                    <a href="{{ route('admin.teachers.index') }}" class="{{ request()->routeIs('admin.teachers.*') ? 'active' : '' }}">
                        <x-icon name="users" /> Guru
                    </a>
                    <a href="{{ route('admin.classrooms.index') }}" class="{{ request()->routeIs('admin.classrooms.*') ? 'active' : '' }}">
                        <x-icon name="school" /> Kelas
                    </a>
                    <a href="{{ route('admin.subjects.index') }}" class="{{ request()->routeIs('admin.subjects.*') ? 'active' : '' }}">
                        <x-icon name="book" /> Mata Pelajaran
                    </a>
                    <a href="{{ route('admin.schedules.index') }}" class="{{ request()->routeIs('admin.schedules.*') ? 'active' : '' }}">
                        <x-icon name="calendar" /> Jadwal
                    </a>
                @else
                <div class="sidebar-label">Data Akademik</div>
                <a href="{{ route('admin.students.index') }}" class="{{ request()->routeIs('admin.students.*') ? 'active' : '' }}">
                    <x-icon name="graduation-cap" /> Siswa
                </a>
                <a href="{{ route('admin.teachers.index') }}" class="{{ request()->routeIs('admin.teachers.*') ? 'active' : '' }}">
                    <x-icon name="users" /> Guru
                </a>
                <a href="{{ route('admin.guardians.index') }}" class="{{ request()->routeIs('admin.guardians.*') ? 'active' : '' }}">
                    <x-icon name="user" /> Wali Murid
                </a>
                <a href="{{ route('admin.classrooms.index') }}" class="{{ request()->routeIs('admin.classrooms.*') ? 'active' : '' }}">
                    <x-icon name="school" /> Kelas
                </a>
                <a href="{{ route('admin.subjects.index') }}" class="{{ request()->routeIs('admin.subjects.*') ? 'active' : '' }}">
                    <x-icon name="book" /> Mata Pelajaran
                </a>
                <a href="{{ route('admin.schedules.index') }}" class="{{ request()->routeIs('admin.schedules.*') ? 'active' : '' }}">
                    <x-icon name="calendar" /> Jadwal
                </a>
                <a href="{{ route('admin.journals.index') }}" class="{{ request()->routeIs('admin.journals.*') ? 'active' : '' }}">
                    <x-icon name="book-open" /> Jurnal Mengajar
                </a>
                <div class="sidebar-label">Akademik &amp; Prestasi</div>
                <a href="{{ route('admin.curriculum-groups.index') }}" class="{{ request()->routeIs('admin.curriculum-groups.*') ? 'active' : '' }}">
                    <x-icon name="book-open" /> Kelompok Mapel
                </a>
                <a href="{{ route('admin.academic-events.index') }}" class="{{ request()->routeIs('admin.academic-events.*') ? 'active' : '' }}">
                    <x-icon name="calendar" /> Kalender Akademik
                </a>
                <a href="{{ route('admin.achievements.index') }}" class="{{ request()->routeIs('admin.achievements.*') ? 'active' : '' }}">
                    <x-icon name="award" /> Prestasi
                </a>
                <a href="{{ route('admin.alumni.index') }}" class="{{ request()->routeIs('admin.alumni.*') ? 'active' : '' }}">
                    <x-icon name="graduation-cap" /> Info PTN / Alumni
                </a>
                <div class="sidebar-label">Kelembagaan</div>
                <a href="{{ route('admin.staff.index') }}" class="{{ request()->routeIs('admin.staff.*') ? 'active' : '' }}">
                    <x-icon name="briefcase" /> Tata Usaha
                </a>
                <a href="{{ route('admin.organization.index') }}" class="{{ request()->routeIs('admin.organization.*') ? 'active' : '' }}">
                    <x-icon name="network" /> Struktur Organisasi
                </a>
                <a href="{{ route('admin.facilities.index') }}" class="{{ request()->routeIs('admin.facilities.*') ? 'active' : '' }}">
                    <x-icon name="school" /> Fasilitas Sekolah
                </a>
                <div class="sidebar-label">Kesiswaan</div>
                <a href="{{ route('admin.extracurriculars.index') }}" class="{{ request()->routeIs('admin.extracurriculars.*') ? 'active' : '' }}">
                    <x-icon name="award" /> Ekstrakurikuler
                </a>
                <a href="{{ route('admin.student-organizations.index') }}" class="{{ request()->routeIs('admin.student-organizations.*') ? 'active' : '' }}">
                    <x-icon name="users" /> Organisasi Siswa
                </a>
                <a href="{{ route('admin.counseling-services.index') }}" class="{{ request()->routeIs('admin.counseling-services.*') ? 'active' : '' }}">
                    <x-icon name="message" /> Bimbingan Konseling
                </a>
                <div class="sidebar-label">Konten & Sistem</div>
                <a href="{{ route('admin.announcements.index') }}" class="{{ request()->routeIs('admin.announcements.*') ? 'active' : '' }}">
                    <x-icon name="megaphone" /> Berita & Pengumuman
                </a>
                <a href="{{ route('admin.writings.index') }}" class="{{ request()->routeIs('admin.writings.*') ? 'active' : '' }}">
                    <x-icon name="book-open" /> Majalah / Artikel Siswa
                </a>
                <a href="{{ route('admin.contact-messages.index') }}" class="{{ request()->routeIs('admin.contact-messages.*') ? 'active' : '' }}">
                    <x-icon name="mail" /> Pesan & Saran
                </a>
                @if(auth()->user()->isAdmin())
                    <a href="{{ route('admin.users.index') }}" class="{{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                        <x-icon name="user" /> Pengguna Panel
                    </a>
                    <a href="{{ route('admin.settings.edit') }}" class="{{ request()->routeIs('admin.settings.*', 'admin.backups.*', 'admin.restore.*') ? 'active' : '' }}">
                        <x-icon name="settings" /> Pengaturan
                    </a>
                @endif
                @endif
            @else
                <a href="{{ route('guru.dashboard') }}" class="{{ request()->routeIs('guru.dashboard') ? 'active' : '' }}">
                    <x-icon name="dashboard" /> Dashboard
                </a>
                <a href="{{ route('guru.grades.index') }}" class="{{ request()->routeIs('guru.grades.*') ? 'active' : '' }}">
                    <x-icon name="chart" /> Input Nilai
                </a>
                <a href="{{ route('guru.journals.index') }}" class="{{ request()->routeIs('guru.journals.*') ? 'active' : '' }}">
                    <x-icon name="book-open" /> Jurnal Mengajar
                </a>
                @if(auth()->user()->teacher?->classrooms()->exists())
                    <a href="{{ route('guru.attendance.index') }}" class="{{ request()->routeIs('guru.attendance.*') ? 'active' : '' }}">
                        <x-icon name="clipboard" /> Absensi
                    </a>
                @endif
            @endif
            <a href="{{ route('home') }}" target="_blank" rel="noopener">
                <x-icon name="arrow-right" /> Lihat Website
            </a>
        </nav>

        <div class="sidebar-foot">
            <strong>{{ auth()->user()->name }}</strong>
            <span class="role">{{ auth()->user()->roleLabel() }}</span>
        </div>
    </aside>

    <div class="admin-main">
        <header class="admin-topbar">
            <div class="crumb">Panel / <strong>@yield('title', 'Dashboard')</strong></div>
            <div class="admin-topbar-actions">
                <div class="admin-user">
                    <span class="dot"></span>
                    <span>{{ auth()->user()->name }}</span>
                    <div class="avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
                </div>
                <form method="POST" action="{{ route('logout') }}" class="admin-logout">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline" title="Keluar" aria-label="Keluar">
                        <x-icon name="log-out" size="14px" /><span>Keluar</span>
                    </button>
                </form>
            </div>
        </header>

        <div class="admin-content">
            @include('partials.flash')
            @yield('content')
        </div>

        <footer class="admin-footer">
            <span>&copy; {{ now()->year }} {{ $school['school_name'] ?? 'Sekolah' }} · Panel Administrasi</span>
            <span class="admin-footer-links">
                <a href="{{ route('home') }}" target="_blank" rel="noopener">Lihat website</a>
                @if(auth()->user()->isAdmin())
                    <a href="{{ route('admin.users.index') }}">Pengguna panel</a>
                    <a href="{{ route('admin.settings.edit') }}">Pengaturan</a>
                @endif
            </span>
        </footer>
    </div>
</div>
<script src="{{ asset('js/admin.js') }}" defer></script>
</body>
</html>
