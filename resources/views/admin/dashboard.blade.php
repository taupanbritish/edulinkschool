@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
<div class="page-head">
    <div>
        <h1>Selamat datang, {{ auth()->user()->name }} 👋</h1>
        <p>Ringkasan aktivitas akademik {{ $school['school_short'] ?? 'sekolah' }} — {{ now()->locale('id')->translatedFormat('l, d F Y') }}</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('admin.announcements.create') }}" class="btn btn-primary btn-sm"><x-icon name="plus" size="15px" /> Buat Pengumuman</a>
        <a href="{{ route('home') }}" target="_blank" rel="noopener" class="btn btn-outline btn-sm keep-readonly">Lihat Website</a>
    </div>
</div>

<div class="stat-grid-admin">
    <div class="stat-card">
        <div class="label">Siswa Aktif <span class="stat-icon"><x-icon name="graduation-cap" size="15px" /></span></div>
        <div class="value">{{ number_format($stats['students'], 0, ',', '.') }}</div>
        <div class="sub">Terdaftar pada tahun pelajaran berjalan</div>
    </div>
    <div class="stat-card">
        <div class="label">Guru <span class="stat-icon"><x-icon name="users" size="15px" /></span></div>
        <div class="value">{{ $stats['teachers'] }}</div>
        <div class="sub">Tenaga pendidik aktif</div>
    </div>
    <div class="stat-card">
        <div class="label">Tata Usaha <span class="stat-icon"><x-icon name="briefcase" size="15px" /></span></div>
        <div class="value">{{ $stats['staff_active'] }}</div>
        <div class="sub">{{ $stats['staff_total'] }} staf tercatat · {{ $stats['staff_inactive'] }} nonaktif</div>
    </div>
    <div class="stat-card">
        <div class="label">Kelas <span class="stat-icon"><x-icon name="school" size="15px" /></span></div>
        <div class="value">{{ $stats['classrooms'] }}</div>
        <div class="sub">Rombongan belajar</div>
    </div>
    <div class="stat-card">
        <div class="label">Kehadiran 30 Hari <span class="stat-icon"><x-icon name="check" size="15px" /></span></div>
        <div class="value">{{ $stats['attendance_rate'] }}%</div>
        <div class="sub">Rata-rata kehadiran seluruh siswa</div>
    </div>
</div>

<div class="section-split">
    <div class="stack">
        <div class="panel">
            <div class="panel-head">
                <h2>Jadwal Hari Ini</h2>
                <a href="{{ route('admin.schedules.index') }}" class="btn btn-ghost btn-sm">Kelola jadwal <x-icon name="arrow-right" size="14px" /></a>
            </div>
            <div class="panel-body flush">
                @forelse($todaySchedules as $schedule)
                    <div class="today-item">
                        <div class="today-time">{{ substr($schedule->starts_at, 0, 5) }} – {{ substr($schedule->ends_at, 0, 5) }}</div>
                        <div style="flex:1">
                            <strong>{{ $schedule->assignment->subject->name }}</strong>
                            <span>{{ $schedule->assignment->classroom->name }} · {{ $schedule->assignment->teacher->name }} · Ruang {{ $schedule->room ?? '-' }}</span>
                        </div>
                        <span class="badge badge-neutral">{{ $schedule->assignment->classroom->name }}</span>
                    </div>
                @empty
                    <div class="empty-state">Tidak ada jadwal mengajar hari ini.</div>
                @endforelse
            </div>
        </div>

        <div class="panel">
            <div class="panel-head">
                <h2>Siswa Terbaru</h2>
                <a href="{{ route('admin.students.index') }}" class="btn btn-ghost btn-sm">Semua siswa <x-icon name="arrow-right" size="14px" /></a>
            </div>
            <div class="panel-body flush">
                <div class="table-wrap">
                    <table class="data">
                        <thead>
                            <tr><th>Nama</th><th>NIS</th><th>Kelas</th><th>Wali Murid</th></tr>
                        </thead>
                        <tbody>
                            @foreach($recentStudents as $student)
                                <tr>
                                    <td><span class="cell-main">{{ $student->name }}</span></td>
                                    <td>{{ $student->nis }}</td>
                                    <td>{{ $student->classroom->name }}</td>
                                    <td>{{ $student->guardian?->name ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="stack">
        <div class="panel">
            <div class="panel-head"><h2>Kehadiran Hari Ini</h2></div>
            <div class="panel-body">
                <div class="attendance-summary">
                    @foreach(\App\Models\Attendance::STATUSES as $status => $label)
                        <div class="attendance-pill">
                            <strong>{{ $attendanceToday[$status] ?? 0 }}</strong>
                            <span>{{ $label }}</span>
                        </div>
                    @endforeach
                </div>
                <p class="muted mt-2" style="font-size:.78rem;margin-bottom:0">Absensi diisi oleh guru melalui ruang guru. Data di atas dihitung dari absensi hari ini.</p>
            </div>
        </div>

        <div class="panel">
            <div class="panel-head">
                <h2>Berita Terbaru</h2>
                <a href="{{ route('admin.announcements.index') }}" class="btn btn-ghost btn-sm">Kelola <x-icon name="arrow-right" size="14px" /></a>
            </div>
            <div class="panel-body flush">
                @forelse($latestAnnouncements as $item)
                    <div class="today-item">
                        <div style="flex:1">
                            <strong>{{ $item->title }}</strong>
                            <span>{{ $item->published_at?->locale('id')->translatedFormat('d M Y') ?? 'Belum dipublikasikan' }}</span>
                        </div>
                        <span class="badge {{ $item->is_published ? 'badge-success' : 'badge-neutral' }}">{{ $item->is_published ? 'Terbit' : 'Draf' }}</span>
                    </div>
                @empty
                    <div class="empty-state">Belum ada berita.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>

@endsection
