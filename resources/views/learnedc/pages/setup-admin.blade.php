<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Setup Admin - Learn's EDC</title>
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
        <p class="brand-subtitle">Buat akun Admin Utama untuk memulai pengelolaan sekolah.</p>
        <form class="login-form">
            <div class="form-group"><label for="nama_admin" class="form-label">Nama Admin Utama</label><input id="nama_admin" class="form-input" placeholder="Masukkan nama lengkap..."></div>
            <div class="form-group"><label for="username" class="form-label">Username</label><input id="username" class="form-input" placeholder="Buat username..."></div>
            <div class="form-group"><label for="email" class="form-label">Email (Opsional)</label><input id="email" type="email" class="form-input" placeholder="Masukkan email..."></div>
            <div class="form-group"><label for="password" class="form-label">Password (Minimal 10 Karakter)</label><input id="password" type="password" class="form-input" placeholder="Buat password..."></div>
            <button class="btn-submit" type="button"><span class="btn-icon">&#x1F510;</span><span>Buat Akun Admin Utama</span></button>
        </form>
        <footer class="card-footer">Learn's EDC &middot; Sistem Informasi Manajemen Sekolah</footer>
    </main>
</body>
</html>
