@php
    $pengguna = auth()->user();
    $menu = \App\Support\Menu::untuk($pengguna);
    $inisial = \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($pengguna->name, 0, 1));
    $sekarang = now()->locale('id');
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Learn's EDC</title>

    <link rel="icon" href="{{ asset('images/learnedc/logo.png') }}">
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome/css/all.min.css') }}">

    {{-- Tailwind (dipakai isi halaman dan komponen Breeze) dimuat lebih dulu, desain Learn's EDC menimpanya. --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset('css/learnedc/admin.css') }}">
    <link rel="stylesheet" href="{{ asset('css/learnedc/app.css') }}">
    <link rel="stylesheet" href="{{ asset('css/learnedc/bridge.css') }}">
    <script src="{{ asset('js/learnedc/layout.js') }}" defer></script>
</head>
<body>
<div class="admin-wrapper">
    <header class="navbar-admin">
        <div class="navbar-brand-section">
            <button type="button" class="navbar-toggle-btn" aria-label="Buka menu"><i class="fa-solid fa-bars"></i></button>
            <a href="{{ route($pengguna->dashboardRouteName()) }}" class="brand-link">
                <img src="{{ asset('images/learnedc/logo.png') }}" alt="Learn's EDC" class="brand-logo-img">
                <span class="brand-text-group">
                    <span class="brand-title">LEARN'S EDC</span>
                    <span class="brand-subtitle">Education Management</span>
                </span>
            </a>
        </div>

        <div class="navbar-search-section">
            <div class="search-box">
                <i class="fa-solid fa-magnifying-glass search-icon"></i>
                <input class="search-input" type="search" placeholder="Cari data di halaman ini..." aria-label="Cari data di halaman ini">
            </div>
        </div>

        <div class="navbar-actions-section">
            <div class="time-capsule" aria-label="Waktu WIB">
                <span class="time-hour">{{ $sekarang->format('H:i:s') }}</span>
                <span class="time-date">{{ $sekarang->translatedFormat('l, j F Y') }}</span>
            </div>

            <a class="notif-button-wrapper" href="{{ route($pengguna->dashboardRouteName()) }}" title="Beranda" aria-label="Beranda">
                <span class="notif-btn" aria-hidden="true"><i class="fa-solid fa-bell"></i></span>
            </a>

            <div class="profile-section">
                <button type="button" class="profile-display" data-profil-toggle aria-haspopup="true" aria-expanded="false">
                    <span class="profile-avatar-circle">{{ $inisial }}</span>
                    <span class="profile-info">
                        <span class="profile-name">{{ $pengguna->name }}</span>
                        <span class="profile-role">{{ $menu['peran'] }}</span>
                    </span>
                </button>

                <div class="profile-dropdown-menu" id="menu-profil">
                    <a class="dropdown-item" href="{{ route('profile.edit') }}">
                        <i class="fa-solid fa-user-gear"></i> Profil &amp; Kata Sandi
                    </a>
                    <div class="dropdown-divider"></div>
                    <button type="button" class="dropdown-item" data-keluar>
                        <i class="fa-solid fa-arrow-right-from-bracket"></i> Keluar
                    </button>
                </div>
            </div>
        </div>
    </header>

    <div class="admin-body">
        <div class="sidebar-backdrop"></div>
        <aside class="sidebar-admin">
            <div class="sidebar-admin-mobile-header">
                <span class="brand-title">MENU LEARN'S EDC</span>
                <button type="button" class="sidebar-close-btn" aria-label="Tutup menu"><i class="fa-solid fa-xmark"></i></button>
            </div>

            <div class="sidebar-role-card">
                <span class="role-badge-label">ROLE AKTIF</span>
                <span class="role-badge-title">{{ $menu['peran'] }}</span>
            </div>

            @foreach ($menu['seksi'] as $seksi)
                <div class="sidebar-section-title">{{ $seksi['judul'] }}</div>
                <ul class="sidebar-menu-list">
                    @foreach ($seksi['item'] as $item)
                        <li class="sidebar-menu-item">
                            <a class="sidebar-link {{ $item['aktif'] ? 'active' : '' }}" href="{{ $item['url'] }}">
                                <span class="sidebar-icon icon-guru"><i class="fa-solid {{ $item['ikon'] }}"></i></span>
                                <span class="sidebar-text">{{ $item['label'] }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endforeach

            <div class="sidebar-bottom-group">
                <a href="{{ route('profile.edit') }}" class="sidebar-link btn-setting {{ $menu['pengaturanAktif'] ? 'active' : '' }}">
                    <span class="sidebar-icon"><i class="fa-solid fa-gear"></i></span>
                    <span class="sidebar-text">Pengaturan</span>
                </a>
                <button type="button" class="sidebar-link btn-logout" data-keluar>
                    <span class="sidebar-icon"><i class="fa-solid fa-arrow-right-from-bracket"></i></span>
                    <span class="sidebar-text">Keluar (Logout)</span>
                </button>
            </div>
        </aside>

        <main class="admin-content">
            <header class="page-header-banner app-page-header">
                <div class="banner-icon-box"><i class="fa-solid {{ $menu['ikon'] }}"></i></div>
                <div class="banner-title-group banner-slot">
                    @isset($header)
                        {{ $header }}
                    @endisset
                    <p class="banner-subtitle">{{ $menu['peran'] }} · Sistem Informasi Sekolah</p>
                </div>
            </header>

            <div class="app-slot">
                {{ $slot }}
            </div>

            <footer class="footer">Learn's EDC · Sistem Informasi Sekolah</footer>
        </main>
    </div>
</div>

<dialog class="logout-dialog" id="dialog-keluar" aria-labelledby="judul-keluar">
    <div class="logout-dialog-art" aria-hidden="true"><i class="fa-solid fa-arrow-right-from-bracket"></i><span>&#10022;</span></div>
    <p class="logout-dialog-eyebrow">KELUAR</p>
    <h2 id="judul-keluar">Keluar dari akun?</h2>
    <p>Anda perlu masuk kembali untuk membuka Learn's EDC.</p>
    <div class="logout-dialog-actions">
        <button type="button" class="logout-dialog-cancel" data-tutup-dialog>Batal</button>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="logout-dialog-confirm">Ya, keluar</button>
        </form>
    </div>
</dialog>
</body>
</html>
