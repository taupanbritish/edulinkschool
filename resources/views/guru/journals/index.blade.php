@extends('layouts.admin')

@section('title', 'Jurnal Mengajar')

@section('content')
<div class="page-head">
    <div>
        <h1>Jurnal Mengajar</h1>
        <p>Catat materi, kegiatan, tugas, dan kehadiran tiap sesi. Jurnal tampil untuk siswa dan orang tua di aplikasi.</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('guru.journals.create', ['date' => now()->toDateString()]) }}" class="btn btn-primary btn-sm">
            <x-icon name="plus" size="14px" /> Isi Jurnal
        </a>
    </div>
</div>

<div class="panel mb-2">
    <div class="panel-head">
        <form method="GET" action="{{ route('guru.journals.index') }}" class="filter-bar">
            <select name="assignment" class="select" onchange="this.form.submit()">
                <option value="">Semua penugasan</option>
                @foreach($assignments as $assignment)
                    <option value="{{ $assignment->id }}" @selected($selected && $selected->id === $assignment->id)>
                        {{ $assignment->classroom->name }} · {{ $assignment->subject->name }}
                    </option>
                @endforeach
            </select>
            <input type="month" name="month" value="{{ $month->format('Y-m') }}" class="input" onchange="this.form.submit()">
            <noscript><button type="submit" class="btn btn-outline btn-sm">Tampilkan</button></noscript>
            @if($selected || request()->filled('month'))
                <a href="{{ route('guru.journals.index') }}" class="btn btn-ghost btn-sm">Reset</a>
            @endif
        </form>
        <span class="muted" style="font-size:.8rem">{{ $total }} jurnal · {{ $month->locale('id')->translatedFormat('F Y') }}</span>
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
                        <th>Materi</th>
                        <th>Kehadiran</th>
                        <th>Lampiran</th>
                        <th style="text-align:right">Aksi</th>
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
                            <td style="max-width:320px">
                                <span class="cell-main">{{ $journal->topic }}</span>
                                <span class="muted" style="display:block;font-size:.75rem">{{ \Illuminate\Support\Str::limit($journal->activity, 80) }}</span>
                            </td>
                            <td>
                                <span class="badge badge-success">{{ $counts['hadir'] }} hadir</span>
                                @if($counts['sakit'] > 0)<span class="badge badge-info">{{ $counts['sakit'] }} sakit</span>@endif
                                @if($counts['izin'] > 0)<span class="badge badge-announcement">{{ $counts['izin'] }} izin</span>@endif
                                @if($counts['alpa'] > 0)<span class="badge badge-danger">{{ $counts['alpa'] }} alpa</span>@endif
                            </td>
                            <td>{{ $journal->attachments->count() ?: '-' }}</td>
                            <td>
                                <div class="row-actions">
                                    <a href="{{ route('guru.journals.edit', $journal) }}" class="btn btn-sm btn-outline">
                                        <x-icon name="edit" size="13px" /> Detail / Edit
                                    </a>
                                    <form method="POST" action="{{ route('guru.journals.destroy', $journal) }}" class="inline-form" onsubmit="return confirm('Hapus jurnal {{ $journal->topic }}? Tindakan ini tidak dapat dibatalkan.')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger"><x-icon name="trash" size="13px" /></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <div class="empty-state">
                                    Belum ada jurnal pada periode ini.
                                    <a href="{{ route('guru.journals.create', ['date' => now()->toDateString()]) }}">Isi jurnal sesi hari ini</a>.
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @include('partials.pagination', ['paginator' => $journals])
</div>
@endsection
