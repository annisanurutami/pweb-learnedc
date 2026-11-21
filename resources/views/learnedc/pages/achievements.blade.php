@extends('learnedc.layouts.app')

@section('content')
    <section class="panel">
        <div class="panel-heading"><h2>Catat Prestasi Siswa</h2></div>
        <form class="form-grid">
            <label class="field"><span>Siswa</span><select><option>Pilih siswa</option><option>Rafi Nugraha</option></select></label>
            <label class="field"><span>Nama prestasi</span><input placeholder="Nama prestasi"></label>
            <label class="field"><span>Tingkat</span><select><option>Pilih tingkat</option><option>Sekolah</option><option>Kota</option><option>Provinsi</option><option>Nasional</option></select></label>
            <label class="field"><span>Tanggal</span><input type="date" value="2025-11-17"></label>
            <button class="button primary" type="button">Simpan prestasi</button>
        </form>
    </section>
    <section class="panel"><div class="panel-heading"><h2>Prestasi Kelas</h2></div>
        @include('learnedc.components.table', ['headers' => ['Tanggal', 'Siswa', 'Prestasi', 'Tingkat'], 'rows' => [['12 Nov 2025', 'Rafi Nugraha', 'Juara 1 Lomba Pemrograman', 'Kota'], ['5 Nov 2025', 'Nadia Putri', 'Juara 2 Desain Poster', 'Sekolah']], 'actions' => true])
    </section>
    <section class="panel"><div class="panel-heading"><h2>Catatan BK Kelas</h2></div>
        @include('learnedc.components.table', ['headers' => ['Tanggal', 'Siswa', 'Pelanggaran', 'Poin'], 'rows' => [['17 Nov 2025', 'Rafi Nugraha', 'Terlambat masuk sekolah', '5']]])
    </section>
@endsection
