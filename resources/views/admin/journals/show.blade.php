@extends('layouts.admin')

@section('title', 'Detail Jurnal Mengajar')

@section('content')
<div class="page-head">
    <div>
        <h1>Detail Jurnal Mengajar</h1>
        <p>{{ $journal->teachingAssignment->classroom->name }} · {{ $journal->teachingAssignment->subject->name }} · {{ $journal->date->locale('id')->translatedFormat('l, d F Y') }}</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('admin.journals.index') }}" class="btn btn-outline btn-sm keep-readonly"><x-icon name="arrow-left" size="14px" /> Kembali ke Rekap</a>
    </div>
</div>

<div class="panel mb-2">
    <div class="panel-body">
        <div class="journal-meta">
            <div><span>Tanggal</span><strong>{{ $journal->date->locale('id')->translatedFormat('d F Y') }} ({{ $journal->dayName() }})</strong></div>
            <div><span>Waktu</span><strong>{{ $journal->timeRange() }}</strong></div>
            <div><span>Kelas</span><strong>{{ $journal->teachingAssignment->classroom->name }}</strong></div>
            <div><span>Mata Pelajaran</span><strong>{{ $journal->teachingAssignment->subject->name }}</strong></div>
            <div><span>Guru</span><strong>{{ $journal->teachingAssignment->teacher->name }}</strong></div>
            <div><span>Ruang</span><strong>{{ $journal->schedule?->room ?: '-' }}</strong></div>
        </div>
    </div>
</div>

<div class="section-split">
    <div class="stack">
        <div class="panel">
            <div class="panel-head"><h2>Materi &amp; Kegiatan</h2></div>
            <div class="panel-body">
                <div class="field mb-2">
                    <label>Materi / topik</label>
                    <strong>{{ $journal->topic }}</strong>
                </div>
                <div class="field mb-2">
                    <label>Kegiatan pembelajaran</label>
                    <p style="margin:0;font-size:.9rem;line-height:1.6">{!! nl2br(e($journal->activity)) !!}</p>
                </div>
                <div class="form-grid">
                    <div class="field">
                        <label>PR / tugas</label>
                        <p style="margin:0;font-size:.88rem;line-height:1.6">{{ $journal->assignment ?: '-' }}</p>
                    </div>
                    <div class="field">
                        <label>Catatan</label>
                        <p style="margin:0;font-size:.88rem;line-height:1.6">{{ $journal->notes ?: '-' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="panel">
            <div class="panel-head">
                <h2>Kehadiran Siswa</h2>
                <span class="muted" style="font-size:.8rem">{{ $attendances->count() }} siswa tercatat</span>
            </div>
            <div class="panel-body flush">
                @if($attendances->isEmpty())
                    <div class="empty-state">Belum ada data kehadiran pada jurnal ini.</div>
                @else
                    <div class="table-wrap">
                        <table class="data">
                            <thead><tr><th>Siswa</th><th>NIS</th><th>Status</th></tr></thead>
                            <tbody>
                                @foreach($attendances as $attendance)
                                    <tr>
                                        <td><span class="cell-main">{{ $attendance->student?->name ?? '-' }}</span></td>
                                        <td>{{ $attendance->student?->nis ?? '-' }}</td>
                                        <td>
                                            <span class="badge {{ match($attendance->status) { 'hadir' => 'badge-success', 'sakit' => 'badge-info', 'izin' => 'badge-announcement', default => 'badge-danger' } }}">
                                                {{ $attendance->statusLabel() }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="panel">
        <div class="panel-head">
            <h2>Lampiran</h2>
            <span class="muted" style="font-size:.8rem">{{ $journal->attachments->count() }} lampiran</span>
        </div>
        <div class="panel-body">
            @if($journal->attachments->isEmpty())
                <div class="empty-state">Guru belum mengunggah lampiran pada sesi ini.</div>
            @else
                <div class="attachment-grid">
                    @foreach($journal->attachments as $attachment)
                        <a href="{{ $attachment->url() }}" target="_blank" rel="noopener" class="attachment-card">
                            <div class="attachment-thumb">
                                @if($attachment->isPhoto())
                                    <img src="{{ $attachment->url() }}" alt="{{ $attachment->original_name ?: 'Lampiran jurnal' }}">
                                    <span class="attachment-type">Foto</span>
                                @else
                                    <span class="attachment-placeholder"><x-icon name="book-open" size="22px" /> PDF</span>
                                    <span class="attachment-type">Dokumen</span>
                                @endif
                            </div>
                            <span class="attachment-file">{{ $attachment->original_name ?: basename($attachment->path) }}</span>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
