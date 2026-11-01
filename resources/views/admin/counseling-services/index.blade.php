@extends('layouts.admin')

@section('title', 'Bimbingan Konseling')

@section('content')
<div class="page-head">
    <div>
        <h1>Bimbingan Konseling</h1>
        <p>Kelola layanan konsultasi dan program pengembangan karakter siswa.</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('counseling') }}" target="_blank" rel="noopener" class="btn btn-outline btn-sm"><x-icon name="arrow-right" size="14px" /> Lihat halaman</a>
        <a href="{{ route('admin.counseling-services.create') }}" class="btn btn-primary"><x-icon name="plus" size="15px" /> Tambah Layanan</a>
    </div>
</div>

<div class="panel">
    <div class="panel-head">
        <form method="GET" action="{{ route('admin.counseling-services.index') }}" class="filter-bar">
            <input type="search" name="q" value="{{ request('q') }}" class="input" placeholder="Cari layanan atau program…">
            <select name="category" class="select">
                <option value="">Semua kategori</option>
                @foreach(\App\Models\CounselingService::CATEGORIES as $value => $label)
                    <option value="{{ $value }}" @selected(request('category') === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-outline btn-sm"><x-icon name="search" size="14px" /> Cari</button>
            @if(request('q') || request('category'))
                <a href="{{ route('admin.counseling-services.index') }}" class="btn btn-ghost btn-sm">Reset</a>
            @endif
        </form>
        <span class="muted" style="font-size:.8rem">{{ $services->total() }} data</span>
    </div>
    <div class="panel-body flush">
        <div class="table-wrap">
            <table class="data">
                <thead>
                    <tr><th>Layanan / Program</th><th>Kategori</th><th>Jadwal</th><th>Kontak</th><th>Status</th><th style="text-align:right">Aksi</th></tr>
                </thead>
                <tbody>
                    @forelse($services as $service)
                        <tr>
                            <td>
                                <span class="cell-main">{{ $service->title }}</span>
                                <span class="cell-sub">{{ $service->description ? \Illuminate\Support\Str::limit($service->description, 90) : 'Tanpa keterangan' }}</span>
                            </td>
                            <td><span class="badge {{ $service->category === 'layanan' ? 'badge-info' : 'badge-announcement' }}">{{ $service->categoryLabel() }}</span></td>
                            <td>{{ $service->schedule ?? '-' }}</td>
                            <td>{{ $service->contact ?? '-' }}</td>
                            <td><span class="badge {{ $service->is_active ? 'badge-success' : 'badge-neutral' }}">{{ $service->is_active ? 'Tampil' : 'Tersembunyi' }}</span></td>
                            <td>
                                <div class="row-actions">
                                    <a href="{{ route('admin.counseling-services.edit', $service) }}" class="btn btn-sm btn-outline"><x-icon name="edit" size="13px" /> Ubah</a>
                                    <form method="POST" action="{{ route('admin.counseling-services.destroy', $service) }}" class="inline-form" onsubmit="return confirm('Hapus layanan ini?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger"><x-icon name="trash" size="13px" /></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6"><div class="empty-state">Belum ada layanan bimbingan konseling.</div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @include('partials.pagination', ['paginator' => $services])
    </div>
</div>
@endsection
