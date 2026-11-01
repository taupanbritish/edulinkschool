@extends('layouts.admin')

@section('title', 'Jadwal Pelajaran')

@section('content')
<div class="page-head">
    <div>
        <h1>Jadwal Pelajaran</h1>
        <p>Jadwal per kelas dan hari. Perubahan langsung tampil di aplikasi siswa.</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('admin.schedules.create') }}" class="btn btn-primary"><x-icon name="plus" size="15px" /> Tambah Jadwal</a>
    </div>
</div>

<div class="panel">
    <div class="panel-head">
        <form method="GET" action="{{ route('admin.schedules.index') }}" class="filter-bar">
            <select name="classroom" class="select">
                <option value="">Semua kelas</option>
                @foreach($classrooms as $classroom)
                    <option value="{{ $classroom->id }}" @selected(request('classroom') == $classroom->id)>{{ $classroom->label() }}</option>
                @endforeach
            </select>
            <select name="day" class="select">
                <option value="">Semua hari</option>
                @foreach(\App\Models\Schedule::DAYS as $value => $label)
                    <option value="{{ $value }}" @selected(request('day') == $value)>{{ $label }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-outline btn-sm">Terapkan</button>
            @if(request()->hasAny(['classroom', 'day']))
                <a href="{{ route('admin.schedules.index') }}" class="btn btn-ghost btn-sm">Reset</a>
            @endif
        </form>
        <span class="muted" style="font-size:.8rem">{{ $schedules->total() }} jadwal</span>
    </div>
    <div class="panel-body flush">
        <div class="table-wrap">
            <table class="data">
                <thead>
                    <tr><th>Hari</th><th>Jam</th><th>Kelas</th><th>Mata Pelajaran</th><th>Guru</th><th>Ruang</th><th style="text-align:right">Aksi</th></tr>
                </thead>
                <tbody>
                    @forelse($schedules as $schedule)
                        <tr>
                            <td><span class="badge badge-info">{{ $schedule->dayName() }}</span></td>
                            <td>{{ substr($schedule->starts_at, 0, 5) }} – {{ substr($schedule->ends_at, 0, 5) }}</td>
                            <td>{{ $schedule->assignment->classroom->name }}</td>
                            <td><span class="cell-main">{{ $schedule->assignment->subject->name }}</span></td>
                            <td>{{ $schedule->assignment->teacher->name }}</td>
                            <td>{{ $schedule->room ?? '-' }}</td>
                            <td>
                                <div class="row-actions">
                                    <a href="{{ route('admin.schedules.edit', $schedule) }}" class="btn btn-sm btn-outline"><x-icon name="edit" size="13px" /> Ubah</a>
                                    <form method="POST" action="{{ route('admin.schedules.destroy', $schedule) }}" class="inline-form" onsubmit="return confirm('Hapus jadwal ini?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger"><x-icon name="trash" size="13px" /></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7"><div class="empty-state">Belum ada jadwal pada filter ini.</div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @include('partials.pagination', ['paginator' => $schedules])
    </div>
</div>
@endsection
