@extends('layouts.admin')

@section('title', 'Jurnal Mengajar')

@section('content')
<div class="page-head">
    <div>
        <h1>{{ $mode === 'edit' ? 'Detail Jurnal Mengajar' : 'Isi Jurnal Mengajar' }}</h1>
        <p>{{ $mode === 'edit' ? 'Perbarui materi, kehadiran, atau lampiran sesi ini.' : 'Pilih tanggal, lalu isi jurnal untuk sesi yang sudah berlangsung.' }}</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('guru.journals.index') }}" class="btn btn-outline btn-sm"><x-icon name="arrow-left" size="14px" /> Daftar Jurnal</a>
        @if($mode === 'edit' && $journal)
            <form method="POST" action="{{ route('guru.journals.destroy', $journal) }}" class="inline-form" onsubmit="return confirm('Hapus jurnal {{ $journal->topic }}? Tindakan ini tidak dapat dibatalkan.')">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-sm btn-danger"><x-icon name="trash" size="13px" /> Hapus Jurnal</button>
            </form>
        @endif
    </div>
</div>

@if($mode === 'create' && ! $schedule)
    <div class="panel mb-2">
        <div class="panel-head">
            <form method="GET" action="{{ route('guru.journals.create') }}" class="filter-bar">
                <input type="date" name="date" value="{{ $date }}" max="{{ now()->toDateString() }}" class="input" onchange="this.form.submit()">
                <noscript><button type="submit" class="btn btn-outline btn-sm">Tampilkan</button></noscript>
            </form>
            <span class="muted" style="font-size:.8rem">{{ \Carbon\Carbon::parse($date)->locale('id')->translatedFormat('l, d F Y') }}</span>
        </div>
    </div>

    <div class="panel">
        <div class="panel-head"><h2>Sesi Mengajar</h2></div>
        <div class="panel-body flush">
            @forelse($schedules as $item)
                <div class="today-item">
                    <div class="today-time">{{ substr($item->starts_at, 0, 5) }} – {{ substr($item->ends_at, 0, 5) }}</div>
                    <div style="flex:1">
                        <strong>{{ $item->assignment->subject->name }}</strong>
                        <span>{{ $item->assignment->classroom->name }} · Ruang {{ $item->room ?: '-' }}</span>
                    </div>
                    @if($existing->has($item->id))
                        <a href="{{ route('guru.journals.edit', $existing->get($item->id)) }}" class="btn btn-sm btn-outline">
                            <x-icon name="edit" size="13px" /> Lihat Jurnal
                        </a>
                    @else
                        <a href="{{ route('guru.journals.create', ['date' => $date, 'schedule' => $item->id]) }}" class="btn btn-sm btn-primary">
                            <x-icon name="plus" size="13px" /> Isi Jurnal
                        </a>
                    @endif
                </div>
            @empty
                <div class="empty-state">Tidak ada jadwal mengajar pada tanggal ini. Pilih tanggal lain atau hubungi admin bila jadwal belum diatur.</div>
            @endforelse
        </div>
    </div>
@else
    @php
        $assignment = $journal?->teachingAssignment ?? $schedule?->assignment;
        $timeRange = $journal ? $journal->timeRange() : substr($schedule->starts_at, 0, 5).' – '.substr($schedule->ends_at, 0, 5);
    @endphp

    <div class="panel mb-2">
        <div class="panel-body">
            <div class="journal-meta">
                <div>
                    <span>Tanggal</span>
                    <strong>{{ \Carbon\Carbon::parse($date)->locale('id')->translatedFormat('l, d F Y') }}</strong>
                </div>
                <div>
                    <span>Kelas</span>
                    <strong>{{ $assignment->classroom->name }}</strong>
                </div>
                <div>
                    <span>Mata Pelajaran</span>
                    <strong>{{ $assignment->subject->name }}</strong>
                </div>
                <div>
                    <span>Waktu</span>
                    <strong>{{ $timeRange }}</strong>
                </div>
                <div>
                    <span>Ruang</span>
                    <strong>{{ $schedule?->room ?: '-' }}</strong>
                </div>
            </div>
        </div>
    </div>

    @if($mode === 'edit' && $journal->attachments->isNotEmpty())
        <div class="panel mb-2">
            <div class="panel-head">
                <h2>Lampiran Tersimpan</h2>
                <span class="muted" style="font-size:.8rem">{{ $journal->attachments->count() }} dari {{ $maxAttachments }} lampiran</span>
            </div>
            <div class="panel-body">
                <div class="attachment-grid">
                    @foreach($journal->attachments as $attachment)
                        <div class="attachment-card">
                            <a href="{{ $attachment->url() }}" target="_blank" rel="noopener" class="attachment-thumb">
                                @if($attachment->isPhoto())
                                    <img src="{{ $attachment->url() }}" alt="{{ $attachment->original_name ?: 'Lampiran jurnal' }}">
                                    <span class="attachment-type">Foto</span>
                                @else
                                    <span class="attachment-placeholder"><x-icon name="book-open" size="22px" /> PDF</span>
                                    <span class="attachment-type">Dokumen</span>
                                @endif
                            </a>
                            <span class="attachment-file">{{ $attachment->original_name ?: basename($attachment->path) }}</span>
                            <form method="POST" action="{{ route('guru.journals.attachments.destroy', [$journal, $attachment]) }}" class="inline-form" onsubmit="return confirm('Hapus lampiran ini?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-ghost attachment-remove"><x-icon name="trash" size="13px" /> Hapus lampiran</button>
                            </form>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <form method="POST" action="{{ $mode === 'edit' ? route('guru.journals.update', $journal) : route('guru.journals.store') }}" enctype="multipart/form-data">
        @csrf
        @if($mode === 'edit')
            @method('PUT')
        @else
            <input type="hidden" name="date" value="{{ $date }}">
            <input type="hidden" name="schedule_id" value="{{ $schedule->id }}">
        @endif

        <div class="panel mb-2">
            <div class="panel-head"><h2>Materi &amp; Kegiatan</h2></div>
            <div class="panel-body">
                <div class="field mb-2">
                    <label for="topic">Materi / topik</label>
                    <input id="topic" name="topic" class="input" value="{{ old('topic', $journal?->topic) }}" maxlength="150" placeholder="Misal: Persamaan linear satu variabel" required>
                </div>
                <div class="field mb-2">
                    <label for="activity">Kegiatan pembelajaran</label>
                    <textarea id="activity" name="activity" class="textarea" placeholder="Uraikan kegiatan: pembukaan, diskusi, latihan, penutup…" required>{{ old('activity', $journal?->activity) }}</textarea>
                </div>
                <div class="form-grid">
                    <div class="field">
                        <label for="assignment">PR / tugas (opsional)</label>
                        <textarea id="assignment" name="assignment" class="textarea" placeholder="Tugas yang harus dikerjakan siswa">{{ old('assignment', $journal?->assignment) }}</textarea>
                    </div>
                    <div class="field">
                        <label for="notes">Catatan (opsional)</label>
                        <textarea id="notes" name="notes" class="textarea" placeholder="Catatan untuk siswa, orang tua, atau wali kelas">{{ old('notes', $journal?->notes) }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="panel mb-2">
            <div class="panel-head">
                <h2>Kehadiran Siswa</h2>
                <span class="muted" style="font-size:.8rem">{{ $students->count() }} siswa aktif</span>
            </div>
            <div class="panel-body flush">
                @if($students->isEmpty())
                    <div class="empty-state">Belum ada siswa aktif di kelas ini.</div>
                @else
                    <div class="table-wrap">
                        <table class="data">
                            <thead>
                                <tr>
                                    <th>Siswa</th>
                                    <th>NIS</th>
                                    @foreach($statuses as $label)
                                        <th style="text-align:center">{{ $label }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($students as $student)
                                    @php $current = old('statuses.'.$student->id, $attendances[$student->id] ?? 'hadir'); @endphp
                                    <tr>
                                        <td><span class="cell-main">{{ $student->name }}</span></td>
                                        <td>{{ $student->nis }}</td>
                                        @foreach($statuses as $value => $label)
                                            <td style="text-align:center">
                                                <input type="radio" name="statuses[{{ $student->id }}]" value="{{ $value }}" @checked($current === $value) style="accent-color:var(--teal-600);width:16px;height:16px">
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        <div class="panel mb-2">
            <div class="panel-head">
                <h2>Tambah Lampiran</h2>
                <span class="muted" style="font-size:.8rem">Maksimal {{ $maxAttachments }} lampiran per jurnal</span>
            </div>
            <div class="panel-body">
                <div class="field">
                    <label for="attachments">Foto dokumentasi atau dokumen PDF</label>
                    <input id="attachments" type="file" name="attachments[]" class="input" accept=".jpg,.jpeg,.png,.webp,.pdf" multiple>
                    <span class="hint">Format JPG, PNG, WebP, atau PDF. Maksimal 4 MB per file.</span>
                </div>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary"><x-icon name="check" size="15px" /> {{ $mode === 'edit' ? 'Simpan Perubahan' : 'Simpan Jurnal' }}</button>
            <a href="{{ route('guru.journals.index') }}" class="btn btn-outline">Batal</a>
        </div>
    </form>
@endif
@endsection
