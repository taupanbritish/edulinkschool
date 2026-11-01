@extends('layouts.admin')

@section('title', 'Absensi')

@section('content')
<div class="page-head">
    <div>
        <h1>Absensi Kelas</h1>
        <p>Khusus wali kelas. Isi kehadiran siswa per kelas dan tanggal, data langsung tampil di aplikasi orang tua.</p>
    </div>
    @if($selected)
        <div class="page-actions">
            <a href="{{ route('guru.attendance.export', ['format' => 'pdf', 'classroom' => $selected->id, 'date' => $date]) }}" class="btn btn-outline btn-sm"><x-icon name="download" size="14px" /> Ekspor PDF</a>
            <a href="{{ route('guru.attendance.export', ['format' => 'xlsx', 'classroom' => $selected->id, 'date' => $date]) }}" class="btn btn-outline btn-sm"><x-icon name="download" size="14px" /> Ekspor Excel</a>
        </div>
    @endif
</div>

<div class="panel mb-2">
    <div class="panel-head">
        <form method="GET" action="{{ route('guru.attendance.index') }}" class="filter-bar">
            <select name="classroom" class="select" onchange="this.form.submit()">
                @foreach($classrooms as $classroom)
                    <option value="{{ $classroom->id }}" @selected($selected && $selected->id === $classroom->id)>
                        {{ $classroom->name }}
                    </option>
                @endforeach
            </select>
            <input type="date" name="date" value="{{ $date }}" class="input" onchange="this.form.submit()">
            <noscript><button type="submit" class="btn btn-outline btn-sm">Tampilkan</button></noscript>
        </form>
        @if($selected)
            <span class="muted" style="font-size:.8rem">{{ $students->count() }} siswa · {{ $date }}</span>
        @endif
    </div>
</div>

@if(! $selected)
    <div class="panel"><div class="empty-state">Anda belum ditetapkan sebagai wali kelas. Hubungi admin untuk pengaturan wali kelas.</div></div>
@elseif($students->isEmpty())
    <div class="panel"><div class="empty-state">Belum ada siswa aktif pada kelas ini.</div></div>
@else
    <form method="POST" action="{{ route('guru.attendance.store') }}">
        @csrf
        <input type="hidden" name="classroom_id" value="{{ $selected->id }}">
        <input type="hidden" name="date" value="{{ $date }}">

        <div class="panel">
            <div class="panel-head">
                <h2>{{ $selected->name }} · {{ $date }}</h2>
                <button type="submit" class="btn btn-primary btn-sm"><x-icon name="check" size="14px" /> Simpan Absensi</button>
            </div>
            <div class="panel-body flush">
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
                                @php $current = $existing[$student->id] ?? 'hadir'; @endphp
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
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary"><x-icon name="check" size="15px" /> Simpan Absensi</button>
        </div>
    </form>
@endif
@endsection
