@extends('learnedc.layouts.app')

@section('content')
    <section class="welcome"><div><span class="eyebrow">PORTAL SEKOLAH</span><h2>Selamat datang, Annisa Nurutami</h2><p>Kelola aktivitas belajar dan data sekolah dari satu tempat.</p></div><div class="welcome-icon">✦</div></section>
    <section class="stat-grid">
        <article class="stat-card"><span>Siswa terdaftar</span><strong>248</strong><small>Data dalam sistem</small></article>
        <article class="stat-card"><span>Guru terdaftar</span><strong>32</strong><small>Data dalam sistem</small></article>
        <article class="stat-card"><span>Kelas aktif</span><strong>12</strong><small>Rombongan belajar</small></article>
        <article class="stat-card"><span>Pengumuman</span><strong>5</strong><small>Informasi sekolah</small></article>
    </section>
    <section class="panel"><div class="panel-heading"><div><span class="eyebrow">TERBARU</span><h2>Pengumuman Sekolah</h2></div></div>
        <article class="announcement"><small>17 November 2025</small><b>Persiapan Penilaian Akhir Semester</b><p>Seluruh warga sekolah diharapkan memperhatikan jadwal penilaian akhir semester.</p></article>
        <article class="announcement"><small>14 November 2025</small><b>Kegiatan Literasi Sekolah</b><p>Kegiatan literasi dilaksanakan pada hari Jumat di aula sekolah.</p></article>
    </section>
@endsection
