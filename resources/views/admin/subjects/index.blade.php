@extends('layouts.admin')

@section('title', 'Mata Pelajaran')

@section('content')
<div class="page-head{{ auth()->user()->isWakasekKurikulum() ? ' panel-readonly' : '' }}">
    <div>
        <h1>Mata Pelajaran</h1>
        <p>Daftar mata pelajaran yang diajarkan di sekolah.</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('admin.imports.form', 'mapel') }}" class="btn btn-outline"><x-icon name="upload" size="15px" /> Impor Excel</a>
        <a href="{{ route('admin.subjects.create') }}" class="btn btn-primary"><x-icon name="plus" size="15px" /> Tambah Mapel</a>
    </div>
</div>

<div class="panel{{ auth()->user()->isWakasekKurikulum() ? ' panel-readonly' : '' }}">
    <div class="panel-body flush">
        <div class="table-wrap">
            <table class="data">
                <thead>
                    <tr><th>Kode</th><th>Nama</th><th>Deskripsi</th><th>Jadwal</th><th style="text-align:right">Aksi</th></tr>
                </thead>
                <tbody>
                    @forelse($subjects as $subject)
                        <tr>
                            <td><span class="badge badge-neutral">{{ $subject->code }}</span></td>
                            <td><span class="cell-main">{{ $subject->name }}</span></td>
                            <td class="muted">{{ \Illuminate\Support\Str::limit($subject->description, 70) ?: '-' }}</td>
                            <td>{{ $subject->assignments_count }} penugasan</td>
                            <td>
                                <div class="row-actions">
                                    <a href="{{ route('admin.subjects.edit', $subject) }}" class="btn btn-sm btn-outline"><x-icon name="edit" size="13px" /> Ubah</a>
                                    <form method="POST" action="{{ route('admin.subjects.destroy', $subject) }}" class="inline-form" onsubmit="return confirm({{ \Illuminate\Support\Js::from('Hapus mata pelajaran '.$subject->name.'? Penugasan, jadwal, dan nilai yang terkait juga akan dihapus.') }})">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger"><x-icon name="trash" size="13px" /></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5"><div class="empty-state">Belum ada mata pelajaran.</div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @include('partials.pagination', ['paginator' => $subjects])
    </div>
</div>
@endsection
