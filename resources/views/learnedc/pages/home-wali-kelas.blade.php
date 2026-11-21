@extends('learnedc.layouts.app')

@section('content')
    <section class="welcome"><div><span class="eyebrow">PORTAL WALI KELAS</span><h2>Selamat datang, Annisa Nurutami</h2><p>Ringkasan perkembangan siswa di kelas XI RPL 1.</p></div><div class="welcome-icon">✦</div></section>
    <section class="stat-grid">
        <article class="stat-card"><span>Siswa di kelas</span><strong>32</strong><small>Data siswa aktif di kelas</small></article>
        <article class="stat-card"><span>Presensi tercatat</span><strong>126</strong><small>Seluruh status presensi</small></article>
        <article class="stat-card"><span>Rata-rata nilai</span><strong>84.6</strong><small>Rekap nilai semester</small></article>
        <article class="stat-card"><span>Poin BK / Prestasi</span><strong>12 / 8</strong><small>Total poin BK / jumlah prestasi</small></article>
    </section>
    <section class="panel"><div class="panel-heading"><div><span class="eyebrow">TERBARU</span><h2>Pengumuman Sekolah</h2></div></div>
        <article class="announcement"><small>17 November 2025</small><b>Persiapan Penilaian Akhir Semester</b><p>Jadwal penilaian akhir semester dapat dilihat pada pengumuman sekolah.</p></article>
    </section>
@endsection
