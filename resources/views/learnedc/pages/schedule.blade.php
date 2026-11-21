@extends('learnedc.layouts.app')

@section('content')
    <section class="stat-grid module-stats">
        <article class="stat-card"><span>Jadwal tersimpan</span><strong>28</strong><small>Jam pelajaran</small></article>
        <article class="stat-card"><span>Hari aktif</span><strong>5</strong><small>Dari Senin sampai Jumat</small></article>
        <article class="stat-card"><span>Kelas terjadwal</span><strong>12</strong><small>Rombongan belajar</small></article>
        <article class="stat-card"><span>Mata pelajaran</span><strong>18</strong><small>Dalam jadwal</small></article>
    </section>
    @if ($roleKey === 'admin')
        <section class="panel module-entry-panel">
            <div class="panel-heading"><div><span class="eyebrow">JADWAL KBM</span><h2>Tambah Jadwal Pelajaran</h2></div></div>
            <form class="form-grid">
                <label class="field"><span>Kelas</span><select><option>Pilih kelas</option><option>XI RPL 1</option></select></label>
                <label class="field"><span>Mata pelajaran</span><select><option>Pilih mata pelajaran</option><option>Pemrograman Web</option></select></label>
                <label class="field"><span>Guru</span><select><option>Pilih guru</option><option>Dewi Anggraini</option></select></label>
                <label class="field"><span>Hari</span><select><option>Pilih hari</option><option>Senin</option></select></label>
                <label class="field"><span>Jam mulai</span><input type="time"></label>
                <label class="field"><span>Jam selesai</span><input type="time"></label>
                <button class="button primary" type="button">Simpan jadwal</button>
            </form>
            <p class="form-note">Tahun ajaran 2025/2026 · Ganjil ditetapkan otomatis.</p>
        </section>
    @endif
    <section class="module-list-panel schedule-panel">
        <div class="panel-heading"><div><span class="eyebrow">AKADEMIK</span><h2>{{ $roleKey === 'guru-mapel' ? 'Jadwal Mengajar Saya' : 'Jadwal Pelajaran' }}</h2></div><span class="count">2025/2026 · Ganjil</span></div>
        <section class="panel"><div class="panel-heading"><h2>XI RPL 1</h2></div>
            @include('learnedc.components.table', ['headers' => ['Hari', 'Mulai', 'Selesai', 'Mata pelajaran', 'Guru'], 'rows' => [['Senin', '07:30', '09:00', 'Pemrograman Web', 'Dewi Anggraini'], ['Selasa', '09:30', '11:00', 'Basis Data', 'Budi Santoso'], ['Rabu', '07:30', '09:00', 'Jaringan Komputer', 'Rina Lestari']], 'actions' => $roleKey === 'admin'])
        </section>
    </section>
@endsection
