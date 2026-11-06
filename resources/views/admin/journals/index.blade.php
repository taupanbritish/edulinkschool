@extends('layouts.admin')

@section('title', 'Rekap Jurnal Mengajar')

@section('content')
<div class="page-head">
    <div>
        <h1>Rekap Jurnal Mengajar</h1>
        <p>Pantau keterlaksanaan pembelajaran guru: materi, kegiatan, tugas, kehadiran siswa, dan lampiran.</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('admin.journals.export', ['format' => 'pdf', 'teacher' => $filters['teacher_id'], 'classroom' => $filters['classroom_id'], 'month' => $filters['month']->format('Y-m')]) }}" class="btn btn-outline btn-sm keep-readonly">
            <x-icon name="download" size="14px" /> Ekspor PDF
        </a>
        <a href="{{ route('admin.journals.export', ['format' => 'xlsx', 'teacher' => $filters['teacher_id'], 'classroom' => $filters['classroom_id'], 'month' => $filters['month']->format('Y-m')]) }}" class="btn btn-outline btn-sm keep-readonly">
            <x-icon name="download" size="14px" /> Ekspor Excel
        </a>
    </div>
</div>

<div class="panel mb-2">
    <div class="panel-head">
        <form method="GET" action="{{ route('admin.journals.index') }}" class="filter-bar">
            <select name="teacher" class="select" onchange="this.form.submit()">
                <option value="">Semua guru</option>
                @foreach($teachers as $teacher)
                    <option value="{{ $teacher->id }}" @selected($filters['teacher_id'] === $teacher->id)>{{ $teacher->name }}</option>
                @endforeach
            </select>
            <select name="classroom" class="select" onchange="this.form.submit()">
                <option value="">Semua kelas</option>
                @foreach($classrooms as $classroom)
                    <option value="{{ $classroom->id }}" @selected($filters['classroom_id'] === $classroom->id)>{{ $classroom->name }}</option>
                @endforeach
            </select>
            <input type="month" name="month" value="{{ $filters['month']->format('Y-m') }}" class="input" onchange="this.form.submit()">
            <noscript><button type="submit" class="btn btn-outline btn-sm">Tampilkan</button></noscript>
            @if($filters['teacher_id'] || $filters['classroom_id'] || request()->filled('month'))
                <a href="{{ route('admin.journals.index') }}" class="btn btn-ghost btn-sm">Reset</a>
            @endif
        </form>
        <span class="muted" style="font-size:.8rem">{{ $total }} jurnal · {{ $filters['month']->locale('id')->translatedFormat('F Y') }}</span>
    </div>
</div>

<div class="panel">
    <div class="panel-body flush">
        <div class="table-wrap">
            <table class="data">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Sesi</th>
                        <th>Guru</th>
                        <th>Materi</th>
                        <th>Kehadiran</th>
                        <th>Lampiran</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($journals as $journal)
                        @php $counts = $journal->attendanceCounts(); @endphp
                        <tr>
                            <td>
                                <span class="cell-main">{{ $journal->date->locale('id')->translatedFormat('d M Y') }}</span>
                                <span class="muted" style="display:block;font-size:.75rem">{{ $journal->dayName() }}</span>
                            </td>
                            <td>
                                <span class="cell-main">{{ $journal->teachingAssignment->classroom->name }} · {{ $journal->teachingAssignment->subject->name }}</span>
                                <span class="muted" style="display:block;font-size:.75rem">{{ $journal->timeRange() }}</span>
                            </td>
                            <td>{{ $journal->teachingAssignment->teacher->name }}</td>
                            <td style="max-width:300px">
                                <span class="cell-main">{{ $journal->topic }}</span>
                                <span class="muted" style="display:block;font-size:.75rem">{{ \Illuminate\Support\Str::limit($journal->activity, 70) }}</span>
                            </td>
                            <td>
                                <span class="badge badge-success">{{ $counts['hadir'] }} hadir</span>
                                @if($counts['sakit'] > 0)<span class="badge badge-info">{{ $counts['sakit'] }} sakit</span>@endif
                                @if($counts['izin'] > 0)<span class="badge badge-announcement">{{ $counts['izin'] }} izin</span>@endif
                                @if($counts['alpa'] > 0)<span class="badge badge-danger">{{ $counts['alpa'] }} alpa</span>@endif
                            </td>
                            <td>{{ $journal->attachments->count() ?: '-' }}</td>
                            <td><a href="{{ route('admin.journals.show', $journal) }}" class="btn btn-sm btn-outline">Lihat</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="7"><div class="empty-state">Tidak ada jurnal pada periode dan filter ini.</div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @include('partials.pagination', ['paginator' => $journals])
</div>
@endsection
