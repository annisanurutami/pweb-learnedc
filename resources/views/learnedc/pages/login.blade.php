<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - Learn's EDC</title>
    <link rel="stylesheet" href="{{ asset('css/learnedc/login.css') }}">
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
        <form class="login-form">
            <div class="form-group"><label for="username" class="form-label">Username / NIP / NISN</label><input id="username" name="username" class="form-input" placeholder="Masukkan Username..." autocomplete="username"></div>
            <div class="form-group"><label for="password" class="form-label">Password</label><input id="password" name="password" type="password" class="form-input" placeholder="Masukkan Password..." autocomplete="current-password"></div>
            <button class="btn-submit" type="button"><span class="btn-icon">&#x1F510;</span><span>Masuk ke learnEDC</span></button>
        </form>
        <footer class="card-footer">&copy; {{ date('Y') }} Learn's EDC &middot; Sistem Informasi Manajemen Sekolah</footer>
    </main>
</body>
</html>
