@extends('learnedc.layouts.app')

@section('content')
    <section class="panel">
        <div class="panel-heading"><div><span class="eyebrow">AKUN SAYA</span><h2>Pengaturan Profil</h2></div></div>
        <form class="form-grid one-column profile-settings-form">
            <div class="profile-settings-photo">
                <div class="profile-settings-avatar"><img src="{{ asset('images/learnedc/admin-profile.jpg') }}" alt="Foto profil"></div>
                <div><strong>Foto profil</strong><small>JPG, PNG, atau WebP · Maksimal 5 MB</small></div>
            </div>
            <label class="field"><span>Ganti foto profil</span><input type="file" accept="image/jpeg,image/png,image/webp"></label>
            <label class="field"><span>Nama tampilan</span><input value="Annisa Nurutami"></label>
            <label class="field"><span>Username</span><input value="annisa.nurutami"></label>
            <label class="field"><span>Password baru (opsional)</span><input type="password" placeholder="Kosongkan jika tidak ingin mengganti password"></label>
            <p class="form-note">Username harus unik. Password baru minimal 10 karakter.</p>
            <button class="button primary" type="button">Simpan perubahan profil</button>
        </form>
    </section>
@endsection
