@extends('learnedc.layouts.app')

@section('content')
    <div class="announcement-layout">
        <section class="panel announcement-compose-panel">
            <div class="panel-heading"><div><span class="eyebrow">PENGUMUMAN SEKOLAH</span><h2>Buat Pengumuman Baru</h2></div></div>
            <form class="form-grid one-column announcement-form">
                <label class="field"><span>Judul pengumuman</span><input placeholder="Judul pengumuman"></label>
                <label class="field"><span>Isi pengumuman</span><textarea rows="7" placeholder="Tuliskan informasi untuk warga sekolah..."></textarea></label>
                <label class="field"><span>Tampilkan untuk</span><select><option>Semua warga sekolah</option><option>Guru saja</option></select></label>
                <p class="form-note">Pengumuman hanya untuk guru tidak akan ditampilkan kepada akun siswa.</p>
                <button class="button primary" type="button">Publikasikan pengumuman</button>
            </form>
        </section>
        <section class="panel announcement-history-panel">
            <div class="panel-heading"><div><span class="eyebrow">ARSIP</span><h2>Riwayat Pengumuman</h2></div><span class="count">5 pengumuman</span></div>
            <div class="announcement-list">
                <article class="announcement announcement-card"><small>17-11-2025 08:15 · Semua warga sekolah</small><b>Persiapan Penilaian Akhir Semester</b><p>Seluruh warga sekolah diharapkan memperhatikan jadwal penilaian akhir semester.</p><div class="action-buttons"><a class="btn-action-edit" href="#">Edit</a><button class="btn-action-delete" type="button" aria-label="Hapus"><i class="fa-solid fa-trash"></i></button></div></article>
                <article class="announcement announcement-card"><small>14-11-2025 10:30 · Guru saja</small><b>Rapat Koordinasi Guru</b><p>Rapat koordinasi dilaksanakan pada hari Jumat di ruang guru.</p><div class="action-buttons"><a class="btn-action-edit" href="#">Edit</a><button class="btn-action-delete" type="button" aria-label="Hapus"><i class="fa-solid fa-trash"></i></button></div></article>
            </div>
        </section>
    </div>
@endsection
