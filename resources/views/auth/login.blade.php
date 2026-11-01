@extends('layouts.auth')

@section('title', 'Masuk Portal')

@section('content')
<div class="auth-card">
    <div class="brand" style="margin-bottom:1.6rem">
        <x-brand-mark />
        <div class="brand-text">
            <strong>{{ $school['school_short'] ?? 'Sekolah' }}</strong>
            <span>Portal Akademik</span>
        </div>
    </div>

    <h1>Selamat datang kembali</h1>
    <p>Masuk untuk mengelola data akademik sekolah.</p>

    @if($errors->any())
        <div class="flash flash-error">
            <x-icon name="alert" />
            <span>{{ $errors->first() }}</span>
        </div>
    @endif

    <form method="POST" action="{{ route('login.attempt') }}">
        @csrf
        <div class="field mb-2">
            <label for="email">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" class="input" placeholder="nama@sekolah.id" required autofocus autocomplete="username">
        </div>
        <div class="field mb-2">
            <label for="password">Kata sandi</label>
            <input id="password" type="password" name="password" class="input" placeholder="••••••••" required autocomplete="current-password">
        </div>
        <label class="checkbox mb-2">
            <input type="checkbox" name="remember" value="1"> Ingat saya di perangkat ini
        </label>
        <button type="submit" class="btn btn-primary btn-block">Masuk</button>
    </form>

    <p style="margin-top:1.4rem;font-size:.82rem" class="muted">
        <a href="{{ route('home') }}" style="color:var(--navy-700);font-weight:600">← Kembali ke website sekolah</a>
    </p>
</div>
@endsection
