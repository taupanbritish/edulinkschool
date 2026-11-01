@extends('layouts.admin')

@section('title', 'Dashboard Guru')

@section('content')
<div class="page-head">
    <div>
        <h1>Ruang Guru</h1>
        <p>{{ $teacher->name }} · NIP {{ $teacher->nip }} — {{ now()->locale('id')->translatedFormat('l, d F Y') }}</p>
    </div>
    <div class="page-actions">
        @if($homeroomClassrooms->isNotEmpty())
            <a href="{{ route('guru.attendance.index', ['classroom' => $homeroomClassrooms->first()->id]) }}" class="btn btn-primary btn-sm"><x-icon name="clipboard" size="14px" /> Isi Absensi</a>
        @endif
        <a href="{{ route('guru.grades.index') }}" class="btn btn-outline btn-sm"><x-icon name="chart" size="14px" /> Input Nilai</a>
    </div>
</div>

<div class="stat-grid-admin">
    <div class="stat-card">
        <div class="label">Penugasan Mengajar <span class="stat-icon"><x-icon name="book" size="15px" /></span></div>
        <div class="value">{{ $assignments->count() }}</div>
        <div class="sub">Kelas &amp; mata pelajaran</div>
    </div>
    <div class="stat-card">
        <div class="label">Siswa Diajar <span class="stat-icon"><x-icon name="graduation-cap" size="15px" /></span></div>
        <div class="value">{{ $studentCount }}</div>
        <div class="sub">Siswa aktif di kelas Anda</div>
    </div>
    <div class="stat-card">
        <div class="label">Nilai Tercatat <span class="stat-icon"><x-icon name="chart" size="15px" /></span></div>
        <div class="value">{{ number_format($gradeCount, 0, ',', '.') }}</div>
        <div class="sub">Seluruh penilaian Anda</div>
    </div>
    <div class="stat-card">
        <div class="label">Jadwal Hari Ini <span class="stat-icon"><x-icon name="calendar" size="15px" /></span></div>
        <div class="value">{{ $todaySchedules->count() }}</div>
        <div class="sub">Sesi mengajar</div>
    </div>
</div>

<div class="section-split">
    <div class="stack">
        <div class="panel">
            <div class="panel-head"><h2>Jadwal Mengajar Hari Ini</h2></div>
            <div class="panel-body flush">
                @forelse($todaySchedules as $schedule)
                    <div class="today-item">
                        <div class="today-time">{{ substr($schedule->starts_at, 0, 5) }} – {{ substr($schedule->ends_at, 0, 5) }}</div>
                        <div style="flex:1">
                            <strong>{{ $schedule->assignment->subject->name }}</strong>
                            <span>{{ $schedule->assignment->classroom->name }} · Ruang {{ $schedule->room ?? '-' }}</span>
                        </div>
                        @if($homeroomClassrooms->contains('id', $schedule->assignment->classroom_id))
                            <a href="{{ route('guru.attendance.index', ['classroom' => $schedule->assignment->classroom_id]) }}" class="btn btn-sm btn-outline">Absensi</a>
                        @endif
                    </div>
                @empty
                    <div class="empty-state">Tidak ada jadwal mengajar hari ini.</div>
                @endforelse
            </div>
        </div>

        <div class="panel">
            <div class="panel-head"><h2>Penugasan Mengajar</h2></div>
            <div class="panel-body flush">
                <div class="table-wrap">
                    <table class="data">
                        <thead><tr><th>Kelas</th><th>Mata Pelajaran</th><th>Nilai Tercatat</th><th>Tahun</th></tr></thead>
                        <tbody>
                            @forelse($assignments as $assignment)
                                <tr>
                                    <td><span class="cell-main">{{ $assignment->classroom->name }}</span></td>
                                    <td>{{ $assignment->subject->name }}</td>
                                    <td>{{ $assignment->grades_count }}</td>
                                    <td>{{ $assignment->academic_year }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4"><div class="empty-state">Belum ada penugasan mengajar.</div></td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="panel">
        <div class="panel-head"><h2>Nilai Terbaru</h2></div>
        <div class="panel-body flush">
            @forelse($recentGrades as $grade)
                <div class="today-item">
                    <div style="flex:1">
                        <strong>{{ $grade->student->name }}</strong>
                        <span>{{ $grade->assignment->classroom->name }} · {{ $grade->assignment->subject->name }} · {{ $grade->typeLabel() }}</span>
                    </div>
                    <span class="badge {{ $grade->score >= 75 ? 'badge-success' : 'badge-danger' }}">{{ number_format((float) $grade->score, 0) }}</span>
                </div>
            @empty
                <div class="empty-state">Belum ada nilai yang diinput.</div>
            @endforelse
        </div>
    </div>
</div>
@endsection
