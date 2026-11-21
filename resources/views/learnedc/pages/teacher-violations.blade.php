@extends('learnedc.layouts.app')

@section('content')
    <section class="panel">
        <div class="panel-heading"><h2>Catat Ketertiban &amp; Poin BK</h2></div>
        <form class="form-grid">
            <label class="field"><span>Siswa</span><select><option>Pilih siswa</option><option>Rafi Nugraha</option></select></label>
            <label class="field"><span>Pelanggaran</span><select><option>Pilih pelanggaran</option><option>Terlambat masuk sekolah — 5 poin</option></select></label>
            <label class="field"><span>Tanggal</span><input type="date" value="2025-11-17"></label>
            <button class="button primary" type="button">Simpan catatan</button>
        </form>
        <p class="form-note">Poin BK akan terisi otomatis sesuai jenis pelanggaran yang dipilih.</p>
    </section>
    <section class="panel"><div class="panel-heading"><h2>Riwayat Catatan</h2></div>
        @include('learnedc.components.table', ['headers' => ['Tanggal', 'Siswa', 'Kelas', 'Pelanggaran', 'Poin'], 'rows' => [['17 Nov 2025', 'Rafi Nugraha', 'XI RPL 1', 'Terlambat masuk sekolah', '5'], ['15 Nov 2025', 'Nadia Putri', 'XI RPL 1', 'Tidak memakai atribut lengkap', '3']], 'actions' => true])
    </section>
@endsection
