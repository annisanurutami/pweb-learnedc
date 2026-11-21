@extends('learnedc.layouts.app')

@section('content')
    @if ($roleKey === 'admin')
        <section class="panel">
            <div class="panel-heading"><div><span class="eyebrow">MASTER DATA</span><h2>Tambah Kelas</h2></div></div>
            <form class="form-grid">
                <label class="field"><span>Nama kelas / rombongan</span><input placeholder="Contoh: XI RPL 1"></label>
                <label class="field"><span>Tingkat kelas</span><select><option>Pilih tingkat</option><option>XI</option></select></label>
                <label class="field"><span>Jurusan (opsional)</span><select><option>Pilih jurusan</option><option>Rekayasa Perangkat Lunak</option></select></label>
                <label class="field"><span>Wali kelas (opsional)</span><select><option>Belum ditentukan</option><option>Dewi Anggraini</option></select></label>
                <button class="button primary" type="button">Simpan kelas</button>
            </form>
            <p class="form-note">Pilih tingkat kelas untuk membuat rombongan belajar.</p>
        </section>
    @else
        <section class="stat-grid">
            <article class="stat-card"><span>Siswa di kelas</span><strong>32</strong><small>Data siswa aktif di kelas</small></article>
            <article class="stat-card"><span>Presensi tercatat</span><strong>126</strong><small>Seluruh status presensi</small></article>
            <article class="stat-card"><span>Rata-rata nilai</span><strong>84.6</strong><small>12 rekap nilai</small></article>
            <article class="stat-card"><span>Poin BK / Prestasi</span><strong>12 / 8</strong><small>Total poin BK / jumlah prestasi</small></article>
        </section>
    @endif
    <section class="panel">
        <div class="panel-heading"><div><span class="eyebrow">DATA AKADEMIK</span><h2>{{ $roleKey === 'wali-kelas' ? 'Siswa di kelas yang saya walikan' : 'Daftar Kelas' }}</h2></div></div>
        @include('learnedc.components.table', ['headers' => $roleKey === 'wali-kelas' ? ['Kelas', 'NISN', 'Nama siswa', 'Jenis kelamin'] : ['Nama kelas', 'Tingkat', 'Jurusan', 'Wali kelas', 'Jumlah siswa'], 'rows' => $roleKey === 'wali-kelas' ? [['XI RPL 1', '0085142018', 'Rafi Nugraha', 'L'], ['XI RPL 1', '0085142021', 'Nadia Putri', 'P']] : [['XI RPL 1', 'XI', 'Rekayasa Perangkat Lunak', 'Dewi Anggraini', '32'], ['XII RPL 1', 'XII', 'Rekayasa Perangkat Lunak', 'Budi Santoso', '30']], 'actions' => $roleKey === 'admin'])
    </section>
@endsection
