@extends('layouts.admin')

@section('title', 'Input Nilai')

@section('content')
<div class="page-head">
    <div>
        <h1>Input Nilai</h1>
        <p>Pilih penugasan mengajar, lalu masukkan nilai siswa.</p>
    </div>
    @if($selected)
        <div class="page-actions">
            <a href="{{ route('guru.grades.export', ['format' => 'pdf', 'assignment' => $selected->id, 'q' => $search, 'sort' => $sort]) }}" class="btn btn-outline btn-sm"><x-icon name="download" size="14px" /> Ekspor PDF</a>
            <a href="{{ route('guru.grades.export', ['format' => 'xlsx', 'assignment' => $selected->id, 'q' => $search, 'sort' => $sort]) }}" class="btn btn-outline btn-sm"><x-icon name="download" size="14px" /> Ekspor Excel</a>
        </div>
    @endif
</div>

<div class="panel mb-2">
    <div class="panel-head">
        <form method="GET" action="{{ route('guru.grades.index') }}" class="filter-bar">
            <select name="assignment" class="select" onchange="this.form.submit()">
                @foreach($assignments as $assignment)
                    <option value="{{ $assignment->id }}" @selected($selected && $selected->id === $assignment->id)>
                        {{ $assignment->classroom->name }} · {{ $assignment->subject->name }}
                    </option>
                @endforeach
            </select>
            <input type="search" name="q" value="{{ $search }}" class="input" placeholder="Cari nama siswa…">
            <select name="sort" class="select" onchange="this.form.submit()">
                <option value="terbaru" @selected($sort === 'terbaru')>Urut: Terbaru</option>
                <option value="jenis" @selected($sort === 'jenis')>Urut: Jenis penilaian</option>
            </select>
            <button type="submit" class="btn btn-outline btn-sm"><x-icon name="search" size="14px" /> Cari</button>
            @if($search !== '')
                <a href="{{ route('guru.grades.index', ['assignment' => $selected?->id, 'sort' => $sort]) }}" class="btn btn-ghost btn-sm">Reset</a>
            @endif
        </form>
        @if($selected)
            <span class="muted" style="font-size:.8rem">{{ $students->count() }} siswa · {{ $grades->count() }} nilai tercatat</span>
        @endif
    </div>
</div>

@if(! $selected)
    <div class="panel"><div class="empty-state">Anda belum memiliki penugasan mengajar.</div></div>
@else
    <div class="section-split">
        <div class="panel">
            <div class="panel-head"><h2>Riwayat Nilai — {{ $selected->classroom->name }} · {{ $selected->subject->name }}</h2></div>
            <div class="panel-body flush">
                <div class="table-wrap">
                    <table class="data">
                        <thead><tr><th>Siswa</th><th>Jenis</th><th class="num">Nilai</th><th>Tanggal</th><th>Catatan</th><th style="text-align:right">Aksi</th></tr></thead>
                        <tbody>
                            @forelse($grades as $grade)
                                <tr>
                                    <td><span class="cell-main">{{ $grade->student->name }}</span></td>
                                    <td>{{ $grade->typeLabel() }}</td>
                                    <td class="num"><strong>{{ number_format((float) $grade->score, 0) }}</strong></td>
                                    <td>{{ $grade->recorded_at->locale('id')->translatedFormat('d M Y') }}</td>
                                    <td class="muted">{{ $grade->note ?: '-' }}</td>
                                    <td>
                                        <div class="row-actions">
                                            <form method="POST" action="{{ route('guru.grades.destroy', $grade) }}" class="inline-form" onsubmit="return confirm('Hapus nilai ini?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger"><x-icon name="trash" size="13px" /></button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6"><div class="empty-state">{{ $search !== '' ? 'Tidak ada nilai yang cocok dengan pencarian.' : 'Belum ada nilai untuk penugasan ini.' }}</div></td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="panel">
            <div class="panel-head"><h2>Tambah Nilai</h2></div>
            <div class="panel-body">
                <form method="POST" action="{{ route('guru.grades.store') }}">
                    @csrf
                    <input type="hidden" name="teaching_assignment_id" value="{{ $selected->id }}">
                    <div class="field mb-2">
                        <label for="student_id">Siswa</label>
                        <select id="student_id" name="student_id" class="select" required>
                            @foreach($students as $student)
                                <option value="{{ $student->id }}">{{ $student->name }} ({{ $student->nis }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-grid">
                        <div class="field">
                            <label for="type">Jenis penilaian</label>
                            <select id="type" name="type" class="select">
                                @foreach($types as $value => $label)
                                    <option value="{{ $value }}" @selected(old('type') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="field">
                            <label for="score">Nilai (0–100)</label>
                            <input id="score" type="number" step="0.01" min="0" max="100" name="score" class="input" value="{{ old('score') }}" required>
                        </div>
                    </div>
                    <div class="field mt-2 mb-2">
                        <label for="recorded_at">Tanggal penilaian</label>
                        <input id="recorded_at" type="date" name="recorded_at" class="input" value="{{ old('recorded_at', now()->toDateString()) }}" required>
                    </div>
                    <div class="field mb-2">
                        <label for="note">Catatan (opsional)</label>
                        <input id="note" name="note" class="input" value="{{ old('note') }}" placeholder="Misal: penilaian harian bab 3">
                    </div>
                    <button type="submit" class="btn btn-primary btn-block"><x-icon name="plus" size="15px" /> Simpan Nilai</button>
                </form>
            </div>
        </div>
    </div>
@endif
@endsection
