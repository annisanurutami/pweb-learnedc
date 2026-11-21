@extends('learnedc.layouts.app')

@section('content')
    <section class="welcome"><div><span class="eyebrow">PORTAL GURU PIKET</span><h2>Selamat datang, Annisa Nurutami</h2><p>Informasi jadwal tugas dan catatan ketertiban sekolah.</p></div><div class="welcome-icon">✦</div></section>
    <section class="stat-grid">
        <article class="stat-card"><span>Catatan pelanggaran</span><strong>4</strong><small>Catatan yang dibuat</small></article>
        <article class="stat-card"><span>Poin tercatat</span><strong>15</strong><small>Total poin BK</small></article>
    </section>
    <section class="panel"><div class="panel-heading"><div><span class="eyebrow">JADWAL HARI INI</span><h2>Jadwal Piket</h2></div></div>
        @include('learnedc.components.table', ['headers' => ['Hari', 'Lokasi'], 'rows' => [['Senin', 'Gerbang utama'], ['Rabu', 'Koridor lantai 2']]])
    </section>
    <section class="panel"><div class="panel-heading"><h2>Catatan Ketertiban Terbaru</h2></div>
        @include('learnedc.components.table', ['headers' => ['Tanggal', 'Siswa', 'Catatan', 'Poin'], 'rows' => [['17 Nov 2025', 'Rafi Nugraha', 'Terlambat masuk sekolah', '5'], ['15 Nov 2025', 'Nadia Putri', 'Tidak memakai atribut lengkap', '3']]])
    </section>
@endsection
