<header class="navbar-admin">
    <div class="navbar-brand-section">
        <button type="button" class="navbar-toggle-btn" aria-label="Buka menu"><i class="fa-solid fa-bars"></i></button>
        <a href="{{ url('/learnedc/' . $roleKey) }}" class="brand-link">
            <img src="{{ asset('images/learnedc/logo.png') }}" alt="Learn's EDC" class="brand-logo-img">
            <span class="brand-text-group"><span class="brand-title">LEARN'S EDC</span><span class="brand-subtitle">Education Management</span></span>
        </a>
    </div>
    <div class="navbar-search-section">
        <div class="search-box">
            <i class="fa-solid fa-magnifying-glass search-icon"></i>
            <input class="search-input" type="search" placeholder="Cari data di halaman ini..." aria-label="Cari data di halaman ini">
        </div>
    </div>
    <div class="navbar-actions-section">
        <div class="time-capsule" aria-label="Waktu lokal perangkat">
            <span class="time-hour">08:45:12</span>
            <span class="time-date">Senin, 17 November 2025</span>
        </div>
        <a class="notif-button-wrapper" href="{{ url('/learnedc/' . $roleKey) }}" title="Buka ringkasan dan pengumuman" aria-label="Buka ringkasan dan pengumuman">
            <span class="notif-btn" aria-hidden="true"><i class="fa-solid fa-bell"></i></span>
        </a>
        <div class="profile-section">
            <div class="profile-display">
                <div class="profile-avatar-circle"><img src="{{ asset('images/learnedc/admin-profile.jpg') }}" alt="Foto profil"></div>
                <div class="profile-info"><span class="profile-name">Annisa Nurutami</span><span class="profile-role">{{ $roleLabel }}</span></div>
            </div>
        </div>
    </div>
</header>
