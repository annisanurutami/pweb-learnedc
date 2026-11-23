<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Masuk - Learn's EDC</title>

    <link rel="icon" href="{{ asset('images/learnedc/logo.png') }}">
    <link rel="stylesheet" href="{{ asset('css/learnedc/login.css') }}">
    <style>
        .form-ingat { display: flex; align-items: center; gap: 8px; margin: -4px 0 14px; font-size: .82rem; color: var(--text-label); text-align: left; }
        .form-ingat input { width: 16px; height: 16px; accent-color: var(--primary-purple); }
        .tautan-bantu { display: block; margin-top: 16px; font-size: .8rem; font-weight: 600; color: var(--primary-purple); text-decoration: none; }
        .tautan-bantu:hover { text-decoration: underline; }
        .teks-bantu { margin-bottom: 18px; font-size: .84rem; line-height: 1.5; color: var(--text-subtitle); text-align: left; }
        .form-galat { margin-top: 6px; font-size: .76rem; font-weight: 600; color: #9b1c1c; text-align: left; }
        .btn-tautan { margin-top: 14px; padding: 0; border: 0; background: none; font: inherit; font-size: .8rem; font-weight: 600; color: var(--text-subtitle); cursor: pointer; text-decoration: underline; }
        .baris-tombol { display: flex; flex-direction: column; gap: 4px; }
    </style>
</head>
<body>
    <div class="bg-watermarks" aria-hidden="true">
        <div class="watermark-item wm-top-left"><img src="{{ asset('images/learnedc/logo.png') }}" alt=""><span>LEARN'S EDC</span></div>
        <div class="watermark-item wm-bottom-left"><img src="{{ asset('images/learnedc/logo.png') }}" alt=""><span>LEARN'S EDC</span></div>
        <div class="watermark-item wm-right"><img src="{{ asset('images/learnedc/logo.png') }}" alt=""><span>LEARN'S EDC</span></div>
        <div class="watermark-item wm-bottom-right"><span>LEARN'S EDC</span></div>
    </div>

    <main class="login-card">
        <div class="card-logo"><img src="{{ asset('images/learnedc/logo.png') }}" alt="Logo Learn's EDC"></div>
        <h1 class="brand-title">LEARN'S EDC</h1>
        <p class="brand-subtitle">Education Data Center &mdash; Sistem Manajemen Sekolah</p>

        {{ $slot }}

        <footer class="card-footer">&copy; {{ date('Y') }} Learn's EDC &middot; Sistem Informasi Manajemen Sekolah</footer>
    </main>
</body>
</html>
