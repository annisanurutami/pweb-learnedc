<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $pageTitle }} · Learn's EDC</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/learnedc/navbar.css') }}">
    <link rel="stylesheet" href="{{ asset('css/learnedc/sidebar.css') }}">
    <link rel="stylesheet" href="{{ asset('css/learnedc/pages/' . $pageStyle . '.css') }}">
</head>
<body>
<div class="admin-wrapper">
    @include('learnedc.components.navbar')
    <div class="admin-body">
        @include('learnedc.components.sidebar')
        <main class="admin-content module-{{ $pageKey }}">
            <header class="page-header-banner app-page-header">
                <div class="banner-icon-box"><i class="fa-solid {{ $pageIcon }}"></i></div>
                <div class="banner-title-group">
                    <h1 class="banner-title">{{ $pageTitle }}</h1>
                    <p class="banner-subtitle">{{ $roleLabel }} · Sistem Informasi Sekolah</p>
                </div>
            </header>
            @yield('content')
            <footer class="footer">Learn's EDC · Sistem Informasi Sekolah</footer>
        </main>
    </div>
</div>
</body>
</html>
