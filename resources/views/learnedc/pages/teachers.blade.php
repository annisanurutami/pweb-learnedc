@extends('learnedc.layouts.app')

@section('content')
    <section class="stat-grid module-stats">
        <article class="stat-card"><span>Total akun pengajar</span><strong>32</strong><small>Guru dan petugas terdaftar</small></article>
        <article class="stat-card"><span>Guru</span><strong>28</strong><small>Akun dengan peran ini</small></article>
        <article class="stat-card"><span>Wali Kelas</span><strong>12</strong><small>Akun dengan peran ini</small></article>
    </section>
    <section class="panel module-entry-panel">
        <div class="panel-heading"><div><span class="eyebrow">KELOLA AKUN</span><h2>Tambah Guru</h2></div></div>
        <form class="form-grid">
            <label class="field"><span>NIP</span><input placeholder="Masukkan NIP"></label>
            <label class="field"><span>Nama lengkap</span><input placeholder="Nama guru"></label>
            <label class="field"><span>Username</span><input placeholder="Buat username"></label>
            <label class="field"><span>Password awal</span><input type="password" placeholder="Minimal 10 karakter"></label>
            <label class="field"><span>Email (opsional)</span><input type="email" placeholder="nama@sekolah.sch.id"></label>
            <label class="field"><span>Nomor HP (opsional)</span><input placeholder="08xxxxxxxxxx"></label>
            <button class="button primary" type="button">Simpan akun</button>
        </form>
    </section>
    <section class="panel module-list-panel">
        <div class="panel-heading"><div><span class="eyebrow">MASTER DATA</span><h2>Daftar Guru</h2></div><span class="count">32 akun</span></div>
        @include('learnedc.components.table', ['headers' => ['NIP', 'Nama', 'Username', 'Peran', 'Email', 'No. HP'], 'rows' => [['198705152010011002', 'Dewi Anggraini, S.Pd.', 'dewi.anggraini', 'Guru, Wali Kelas', 'dewi@sekolah.sch.id', '081234567890'], ['198903122014021004', 'Budi Santoso, S.Kom.', 'budi.santoso', 'Guru', 'budi@sekolah.sch.id', '081298765432']], 'actions' => true])
    </section>
@endsection
