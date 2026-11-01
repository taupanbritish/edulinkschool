@extends('layouts.admin')

@section('title', 'Impor '.$label)

@section('content')
<div class="page-head">
    <div>
        <h1>Impor Data {{ $label }}</h1>
        <p>Unggah berkas Excel berisi data {{ strtolower($label) }}. Setiap baris divalidasi sebelum disimpan.</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('admin.imports.template', $type) }}" class="btn btn-outline"><x-icon name="download" size="15px" /> Unduh Template</a>
        <a href="{{ route($indexRoute) }}" class="btn btn-ghost btn-sm"><x-icon name="arrow-left" size="14px" /> Kembali</a>
    </div>
</div>

@if(session('import_result'))
    @php($result = session('import_result'))
    <div class="panel mb-2">
        <div class="panel-head">
            <h2>Hasil Impor</h2>
            <span class="badge {{ $result['imported'] > 0 ? 'badge-success' : 'badge-neutral' }}">{{ $result['imported'] }} baris tersimpan</span>
        </div>
        <div class="panel-body">
            <p style="margin:0">Berhasil mengimpor <strong>{{ $result['imported'] }}</strong> dari {{ $result['total'] }} baris data.</p>
            @if($result['errors'] !== [])
                <div class="import-errors">
                    <strong>{{ count($result['errors']) }} baris dilewati:</strong>
                    <ul>
                        @foreach($result['errors'] as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    </div>
@endif

<div class="panel">
    <div class="panel-head">
        <h2>Unggah Berkas</h2>
        <span class="muted" style="font-size:.78rem">XLSX, XLS, atau CSV · maksimal 5 MB</span>
    </div>
    <div class="panel-body">
        <form method="POST" enctype="multipart/form-data" action="{{ route('admin.imports.store', $type) }}">
            @csrf
            <div class="field">
                <label for="file">Berkas Excel</label>
                <input id="file" type="file" name="file" class="input" accept=".xlsx,.xls,.csv" required>
                <span class="hint">Gunakan template agar nama kolom sesuai. Baris contoh pada template ikut terimpor bila tidak dihapus.</span>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary"><x-icon name="upload" size="15px" /> Mulai Impor</button>
            </div>
        </form>
    </div>
</div>

<div class="panel" style="margin-top:1.2rem">
    <div class="panel-head"><h2>Kolom yang Diharapkan</h2></div>
    <div class="panel-body flush">
        <div class="table-wrap">
            <table class="data">
                <thead><tr><th>Nama kolom</th><th>Keterangan</th></tr></thead>
                <tbody>
                    @foreach($columns as $name => $description)
                        <tr>
                            <td><span class="badge badge-neutral">{{ $name }}</span></td>
                            <td class="muted">{{ $description }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
