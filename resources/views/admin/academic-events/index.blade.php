@extends('layouts.admin')

@section('title', 'Kalender Akademik')

@section('content')
<div class="page-head">
    <div>
        <h1>Kalender Akademik</h1>
        <p>Jadwal kegiatan belajar, ujian, libur semester, dan agenda tahunan sekolah.</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('academic-calendar') }}" target="_blank" rel="noopener" class="btn btn-outline btn-sm"><x-icon name="arrow-right" size="14px" /> Lihat halaman</a>
        <a href="{{ route('admin.academic-events.create') }}" class="btn btn-primary"><x-icon name="plus" size="15px" /> Tambah Agenda</a>
    </div>
</div>

<div class="panel">
    <div class="panel-head">
        <form method="GET" action="{{ route('admin.academic-events.index') }}" class="filter-bar">
            <input type="search" name="q" value="{{ request('q') }}" class="input" placeholder="Cari agenda…">
            <select name="category" class="select">
                <option value="">Semua kategori</option>
                @foreach(\App\Models\AcademicEvent::CATEGORIES as $value => $label)
                    <option value="{{ $value }}" @selected(request('category') === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-outline btn-sm"><x-icon name="search" size="14px" /> Cari</button>
            @if(request('q') || request('category'))
                <a href="{{ route('admin.academic-events.index') }}" class="btn btn-ghost btn-sm">Reset</a>
            @endif
        </form>
        <span class="muted" style="font-size:.8rem">{{ $events->total() }} agenda</span>
    </div>
    <div class="panel-body flush">
        <div class="table-wrap">
            <table class="data">
                <thead>
                    <tr><th>Agenda</th><th>Kategori</th><th>Tanggal</th><th>Status</th><th style="text-align:right">Aksi</th></tr>
                </thead>
                <tbody>
                    @forelse($events as $event)
                        <tr>
                            <td>
                                <span class="cell-main">{{ $event->title }}</span>
                                <span class="cell-sub">{{ $event->description ? \Illuminate\Support\Str::limit($event->description, 90) : 'Tanpa keterangan' }}</span>
                            </td>
                            <td><span class="badge badge-info">{{ $event->categoryLabel() }}</span></td>
                            <td>{{ $event->dateRange() }}</td>
                            <td><span class="badge {{ $event->is_active ? 'badge-success' : 'badge-neutral' }}">{{ $event->is_active ? 'Tampil' : 'Tersembunyi' }}</span></td>
                            <td>
                                <div class="row-actions">
                                    <a href="{{ route('admin.academic-events.edit', $event) }}" class="btn btn-sm btn-outline"><x-icon name="edit" size="13px" /> Ubah</a>
                                    <form method="POST" action="{{ route('admin.academic-events.destroy', $event) }}" class="inline-form" onsubmit="return confirm('Hapus agenda ini?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger"><x-icon name="trash" size="13px" /></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5"><div class="empty-state">Belum ada agenda akademik.</div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @include('partials.pagination', ['paginator' => $events])
    </div>
</div>
@endsection
