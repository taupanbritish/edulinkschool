@extends('layouts.admin')

@section('title', $service->exists ? 'Ubah Layanan BK' : 'Tambah Layanan BK')

@section('content')
<div class="page-head">
    <div>
        <h1>{{ $service->exists ? 'Ubah Layanan BK' : 'Tambah Layanan BK' }}</h1>
        <p>Layanan konsultasi atau program pengembangan karakter yang tampil di halaman Bimbingan Konseling.</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('admin.counseling-services.index') }}" class="btn btn-outline btn-sm"><x-icon name="arrow-left" size="14px" /> Kembali</a>
    </div>
</div>

<form method="POST" action="{{ $service->exists ? route('admin.counseling-services.update', $service) : route('admin.counseling-services.store') }}">
    @csrf
    @if($service->exists) @method('PUT') @endif

    <div class="section-split">
        <div class="stack">
            <div class="panel">
                <div class="panel-head"><h2>Detail Layanan</h2></div>
                <div class="panel-body">
                    <div class="form-grid">
                        <div class="field">
                            <label for="category">Kategori</label>
                            <select id="category" name="category" class="select">
                                @foreach(\App\Models\CounselingService::CATEGORIES as $value => $label)
                                    <option value="{{ $value }}" @selected(old('category', $service->category) === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="field">
                            <label for="schedule">Jadwal</label>
                            <input id="schedule" name="schedule" class="input" value="{{ old('schedule', $service->schedule) }}" placeholder="Senin–Jumat, 08.00–14.00 WIB">
                        </div>
                        <div class="field full">
                            <label for="title">Nama layanan / program</label>
                            <input id="title" name="title" class="input" value="{{ old('title', $service->title) }}" required>
                            @error('title')<span class="error">{{ $message }}</span>@enderror
                        </div>
                        <div class="field full">
                            <label for="description">Keterangan</label>
                            <textarea id="description" name="description" class="textarea">{{ old('description', $service->description) }}</textarea>
                        </div>
                        <div class="field full">
                            <label for="contact">Kontak / lokasi</label>
                            <input id="contact" name="contact" class="input" value="{{ old('contact', $service->contact) }}" placeholder="Ruang BK · guru.bk@sekolah.id">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="stack">
            <div class="panel">
                <div class="panel-head"><h2>Tampilan</h2></div>
                <div class="panel-body">
                    <div class="field mb-2">
                        <label for="sort_order">Urutan tampil</label>
                        <input id="sort_order" type="number" name="sort_order" class="input" value="{{ old('sort_order', $service->sort_order ?? 0) }}" min="0" max="999">
                    </div>
                    <input type="hidden" name="is_active" value="0">
                    <label class="checkbox"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $service->is_active ?? true))> Tampilkan di website</label>
                </div>
            </div>
            <div class="panel">
                <div class="panel-body">
                    <button type="submit" class="btn btn-primary btn-block"><x-icon name="check" size="15px" /> Simpan Layanan</button>
                    <a href="{{ route('admin.counseling-services.index') }}" class="btn btn-ghost btn-block mt-2">Batal</a>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection
