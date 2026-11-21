@extends('learnedc.layouts.app')

@section('content')
    <section class="welcome"><div><span class="eyebrow">PORTAL GURU</span><h2>Selamat datang, Annisa Nurutami</h2><p>Pantau jadwal mengajar, presensi, dan nilai siswa.</p></div><div class="welcome-icon">✦</div></section>
    <section class="stat-grid">
        <article class="stat-card"><span>Jadwal mengajar</span><strong>8</strong><small>Jam pelajaran</small></article>
        <article class="stat-card"><span>Presensi hari ini</span><strong>26</strong><small>Siswa tercatat</small></article>
    </section>
    <section class="panel"><div class="panel-heading"><div><span class="eyebrow">HARI INI</span><h2>Jadwal Mengajar</h2></div></div>
        @include('learnedc.components.table', ['headers' => ['Jam', 'Kelas', 'Mata pelajaran'], 'rows' => [['07:30–09:00', 'X RPL 1', 'Pemrograman Web'], ['09:30–11:00', 'XI RPL 1', 'Basis Data'], ['11:00–12:30', 'X RPL 2', 'Pemrograman Web']]])
    </section>
@endsection
