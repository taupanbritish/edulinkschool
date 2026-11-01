@extends('layouts.admin')

@section('title', $student->exists ? 'Ubah Siswa' : 'Tambah Siswa')

@section('content')
<div class="page-head">
    <div>
        <h1>{{ $student->exists ? 'Ubah Data Siswa' : 'Tambah Siswa' }}</h1>
        <p>{{ $student->exists ? 'Perbarui data identitas siswa.' : 'Lengkapi data identitas siswa baru.' }}</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('admin.students.index') }}" class="btn btn-outline btn-sm"><x-icon name="arrow-left" size="14px" /> Kembali</a>
    </div>
</div>

<form method="POST" enctype="multipart/form-data" action="{{ $student->exists ? route('admin.students.update', $student) : route('admin.students.store') }}">
    @csrf
    @if($student->exists) @method('PUT') @endif

    <div class="panel mb-2">
        <div class="panel-head"><h2>Identitas Siswa</h2></div>
        <div class="panel-body">
            <div class="form-grid">
                <x-photo-field :model="$student" label="Foto siswa" />

                <div class="field">
                    <label for="name">Nama lengkap</label>
                    <input id="name" name="name" class="input" value="{{ old('name', $student->name) }}" required>
                    @error('name')<span class="error">{{ $message }}</span>@enderror
                </div>
                <div class="field">
                    <label for="nis">NIS</label>
                    <input id="nis" name="nis" class="input" value="{{ old('nis', $student->nis) }}" required>
                    @error('nis')<span class="error">{{ $message }}</span>@enderror
                </div>
                <div class="field">
                    <label for="nisn">NISN</label>
                    <input id="nisn" name="nisn" class="input" value="{{ old('nisn', $student->nisn) }}">
                    @error('nisn')<span class="error">{{ $message }}</span>@enderror
                </div>
                <div class="field">
                    <label for="gender">Jenis kelamin</label>
                    <select id="gender" name="gender" class="select">
                        <option value="L" @selected(old('gender', $student->gender) === 'L')>Laki-laki</option>
                        <option value="P" @selected(old('gender', $student->gender) === 'P')>Perempuan</option>
                    </select>
                </div>
                <div class="field">
                    <label for="birth_place">Tempat lahir</label>
                    <input id="birth_place" name="birth_place" class="input" value="{{ old('birth_place', $student->birth_place) }}">
                </div>
                <div class="field">
                    <label for="birth_date">Tanggal lahir</label>
                    <input id="birth_date" type="date" name="birth_date" class="input" value="{{ old('birth_date', $student->birth_date?->format('Y-m-d')) }}">
                </div>
                <div class="field">
                    <label for="classroom_id">Kelas</label>
                    <select id="classroom_id" name="classroom_id" class="select" required>
                        <option value="">Pilih kelas</option>
                        @foreach($classrooms as $classroom)
                            <option value="{{ $classroom->id }}" @selected(old('classroom_id', $student->classroom_id) == $classroom->id)>{{ $classroom->label() }}</option>
                        @endforeach
                    </select>
                    @error('classroom_id')<span class="error">{{ $message }}</span>@enderror
                </div>
                @php
                    $selectedGuardianId = old('guardian_id', $student->guardian_id);
                    $selectedGuardian = filled($selectedGuardianId) ? $guardians->firstWhere('id', $selectedGuardianId) : null;
                @endphp
                <div class="field">
                    <label for="guardian_search">Wali murid</label>
                    <div class="combo" data-combo>
                        <input type="hidden" id="guardian_id" name="guardian_id" value="{{ $selectedGuardian?->id }}" data-combo-value>
                        <div class="combo-control">
                            <x-icon name="search" size="15px" />
                            <input
                                id="guardian_search"
                                type="text"
                                class="input"
                                role="combobox"
                                aria-expanded="false"
                                aria-controls="guardian_options"
                                aria-autocomplete="list"
                                autocomplete="off"
                                placeholder="Cari nama wali murid…"
                                value="{{ $selectedGuardian?->name }}"
                                data-combo-input
                            >
                            <button type="button" class="combo-clear" data-combo-clear aria-label="Kosongkan pilihan" @if(! $selectedGuardian) hidden @endif>×</button>
                        </div>
                        <div class="combo-menu" id="guardian_options" role="listbox" data-combo-menu hidden>
                            <button type="button" class="combo-option" role="option" data-value="" data-label="Belum ditentukan">Belum ditentukan</button>
                            @foreach($guardians as $guardian)
                                <button type="button" class="combo-option{{ $selectedGuardian?->id === $guardian->id ? ' is-selected' : '' }}" role="option" data-value="{{ $guardian->id }}" data-label="{{ $guardian->name }}">
                                    <span class="combo-option-name">{{ $guardian->name }}</span>
                                    @if($guardian->phone)
                                        <span class="combo-sub">{{ $guardian->phone }}</span>
                                    @endif
                                </button>
                            @endforeach
                            <div class="combo-empty" data-combo-empty hidden>Tidak ada wali murid yang cocok.</div>
                        </div>
                    </div>
                    @error('guardian_id')<span class="error">{{ $message }}</span>@enderror
                </div>
                <div class="field">
                    <label for="status">Status</label>
                    <select id="status" name="status" class="select">
                        @foreach(['aktif' => 'Aktif', 'lulus' => 'Lulus', 'pindah' => 'Pindah'] as $value => $label)
                            <option value="{{ $value }}" @selected(old('status', $student->status) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label for="entry_year">Tahun masuk</label>
                    <input id="entry_year" type="number" name="entry_year" class="input" value="{{ old('entry_year', $student->entry_year) }}" min="2000" max="2100" required>
                </div>
                <div class="field full">
                    <label for="address">Alamat</label>
                    <textarea id="address" name="address" class="textarea">{{ old('address', $student->address) }}</textarea>
                </div>
            </div>
        </div>
    </div>

    <div class="panel">
        <div class="panel-head">
            <h2>Akun Login Aplikasi (opsional)</h2>
            <span class="muted" style="font-size:.78rem">Dibutuhkan agar siswa dapat masuk ke aplikasi mobile.</span>
        </div>
        <div class="panel-body">
            <div class="form-grid">
                <div class="field">
                    <label for="email">Email</label>
                    <input id="email" type="email" name="email" class="input" value="{{ old('email', $student->user?->email) }}" placeholder="siswa@sekolah.id">
                    @error('email')<span class="error">{{ $message }}</span>@enderror
                </div>
                <div class="field">
                    <label for="password">Kata sandi</label>
                    <input id="password" type="password" name="password" class="input" placeholder="{{ $student->user ? 'Kosongkan jika tidak diubah' : 'Minimal 6 karakter' }}">
                    @error('password')<span class="error">{{ $message }}</span>@enderror
                </div>
            </div>
        </div>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary"><x-icon name="check" size="15px" /> Simpan</button>
        <a href="{{ route('admin.students.index') }}" class="btn btn-ghost">Batal</a>
    </div>
</form>
@endsection
