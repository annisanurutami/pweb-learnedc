@extends('learnedc.layouts.app')

@section('content')
    <section class="stat-grid module-stats">
        <article class="stat-card"><span>Total siswa</span><strong>248</strong><small>Seluruh tahun ajaran</small></article>
        <article class="stat-card"><span>Laki-laki</span><strong>132</strong><small>Siswa terdaftar</small></article>
        <article class="stat-card"><span>Perempuan</span><strong>116</strong><small>Siswa terdaftar</small></article>
        <article class="stat-card"><span>Rombongan belajar</span><strong>12</strong><small>Kelas tersedia</small></article>
    </section>
    <section class="panel module-entry-panel">
        <div class="panel-heading"><div><span class="eyebrow">PENDAFTARAN</span><h2>Tambah Siswa</h2></div></div>
        <form class="form-grid">
            <label class="field"><span>NISN</span><input placeholder="Masukkan NISN"></label>
            <label class="field"><span>Nama lengkap</span><input placeholder="Nama siswa"></label>
            <label class="field"><span>Username</span><input placeholder="Buat username"></label>
            <label class="field"><span>Password awal</span><input type="password" placeholder="Minimal 10 karakter"></label>
            <label class="field"><span>Jenis kelamin</span><select><option>Pilih</option><option>Laki-laki</option><option>Perempuan</option></select></label>
            <label class="field"><span>Kelas</span><select><option>Pilih kelas</option><option>XI RPL 1</option></select></label>
            <label class="field"><span>Tahun ajaran</span><select><option>2025/2026</option></select></label>
            <label class="field"><span>Semester</span><select><option>Ganjil</option><option>Genap</option></select></label>
            <button class="button primary" type="button">Simpan siswa</button>
        </form>
    </section>
    <section class="panel module-list-panel">
        <div class="panel-heading"><div><span class="eyebrow">MASTER DATA</span><h2>Daftar Siswa</h2></div><span class="count">248 siswa</span></div>
        <h3 class="student-year-heading">Tahun Ajaran 2025/2026</h3><h4 class="student-major-heading">Jurusan: Rekayasa Perangkat Lunak</h4>
        @include('learnedc.components.table', ['headers' => ['NISN', 'Nama', 'Username', 'JK', 'Kelas', 'Semester'], 'rows' => [['0085142018', 'Rafi Nugraha', 'rafi.nugraha', 'L', 'XI RPL 1', 'Ganjil'], ['0085142021', 'Nadia Putri', 'nadia.putri', 'P', 'XI RPL 1', 'Ganjil']], 'actions' => true])
    </section>
@endsection
