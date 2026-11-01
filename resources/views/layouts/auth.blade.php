<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Masuk') — {{ $school['school_short'] ?? 'Sekolah' }}</title>
    <x-favicon />
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body class="auth-page">
    <aside class="auth-aside">
        <div>
            <div class="brand">
                <x-brand-mark />
                <div class="brand-text">
                    <strong style="color:#fff">{{ $school['school_name'] ?? 'Sekolah' }}</strong>
                    <span style="color:#93a7ba">{{ $school['tagline'] ?? '' }}</span>
                </div>
            </div>
            <h2>Satu portal untuk seluruh warga sekolah.</h2>
            <p>Kelola data akademik, jadwal, nilai, absensi, serta berita sekolah dalam satu sistem terpadu.</p>
        </div>
        <div class="auth-quote">
            “Pendidikan adalah senjata paling ampuh untuk mengubah dunia.”
            <div style="margin-top:.5rem;color:#93a7ba;font-size:.8rem">— Nelson Mandela</div>
        </div>
    </aside>

    <main class="auth-main">
        @yield('content')
    </main>
</body>
</html>
