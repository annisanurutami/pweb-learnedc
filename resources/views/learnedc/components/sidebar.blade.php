<div class="sidebar-backdrop"></div>
<aside class="sidebar-admin">
    <div class="sidebar-admin-mobile-header">
        <span class="brand-title">MENU LEARN'S EDC</span>
        <button type="button" class="sidebar-close-btn" aria-label="Tutup menu"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <div class="sidebar-role-card"><span class="role-badge-label">ROLE AKTIF</span><span class="role-badge-title">{{ $roleLabel }}</span></div>
    <div class="sidebar-section-title">MENU UTAMA</div>
    <ul class="sidebar-menu-list">
        @foreach ($menuItems as $key => $item)
            @if ($key !== 'pengaturan')
                <li class="sidebar-menu-item">
                    <a class="sidebar-link {{ $pageKey === $key ? 'active' : '' }}" href="{{ url('/learnedc/' . $roleKey . '/' . $key) }}">
                        <span class="sidebar-icon icon-guru"><i class="fa-solid {{ $item['icon'] }}"></i></span>
                        <span class="sidebar-text">{{ $item['label'] }}</span>
                    </a>
                </li>
            @endif
        @endforeach
    </ul>
    <div class="sidebar-bottom-group">
        <a href="{{ url('/learnedc/' . $roleKey . '/pengaturan') }}" class="sidebar-link btn-setting {{ $pageKey === 'pengaturan' ? 'active' : '' }}">
            <span class="sidebar-icon"><i class="fa-solid fa-gear"></i></span><span class="sidebar-text">Pengaturan</span>
        </a>
        <a href="{{ route('learnedc.login') }}" class="sidebar-link btn-logout">
            <span class="sidebar-icon"><i class="fa-solid fa-arrow-right-from-bracket"></i></span><span class="sidebar-text">Keluar (Logout)</span>
        </a>
    </div>
</aside>
