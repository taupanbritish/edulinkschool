@extends('layouts.admin')

@section('title', 'Struktur Organisasi')

@section('content')
<div class="page-head">
    <div>
        <h1>Struktur Organisasi</h1>
        <p>Atur susunan jabatan sekolah dari kepala sekolah hingga koordinator.</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('admin.organization.create') }}" class="btn btn-primary"><x-icon name="plus" size="15px" /> Tambah Anggota</a>
    </div>
</div>

<div class="panel">
    <div class="panel-head">
        <h2>Susunan Organisasi</h2>
        <span class="muted" style="font-size:.8rem">{{ $total }} anggota</span>
    </div>
    <div class="panel-body flush">
        <div class="table-wrap">
            <table class="data">
                <thead>
                    <tr><th>Nama</th><th>Jabatan</th><th>Urutan</th><th style="text-align:right">Aksi</th></tr>
                </thead>
                <tbody>
                    @forelse($rows as $row)
                        <tr>
                            <td>
                                <div class="cell-photo" style="padding-left:{{ $row['depth'] * 22 }}px">
                                    @if($row['depth'] > 0)
                                        <span class="tree-branch">└</span>
                                    @endif
                                    @if($row['member']->photoUrl())
                                        <img src="{{ $row['member']->photoUrl() }}" alt="" class="thumb">
                                    @else
                                        <span class="thumb thumb-initial">{{ strtoupper(substr($row['member']->name, 0, 1)) }}</span>
                                    @endif
                                    <span class="cell-main">{{ $row['member']->name }}</span>
                                </div>
                            </td>
                            <td>{{ $row['member']->position }}</td>
                            <td>{{ $row['member']->sort_order }}</td>
                            <td>
                                <div class="row-actions">
                                    <a href="{{ route('admin.organization.edit', $row['member']) }}" class="btn btn-sm btn-outline"><x-icon name="edit" size="13px" /> Ubah</a>
                                    <form method="POST" action="{{ route('admin.organization.destroy', $row['member']) }}" class="inline-form" onsubmit="return confirm('Hapus {{ $row['member']->name }} dari struktur organisasi?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger"><x-icon name="trash" size="13px" /></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4"><div class="empty-state">Belum ada anggota struktur organisasi.</div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
