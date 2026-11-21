@extends('learnedc.layouts.app')

@section('content')
    <section class="panel">
        <div class="panel-heading"><div><span class="eyebrow">MASTER DATA</span><h2>Tambah Jenis Pelanggaran</h2></div></div>
        <form class="form-grid">
            <label class="field"><span>Nama pelanggaran</span><input placeholder="Nama pelanggaran"></label>
            <label class="field"><span>Poin BK</span><input type="number" min="0" placeholder="0"></label>
            <button class="button primary" type="button">Tambah jenis pelanggaran</button>
        </form>
        <p class="form-note">Poin akan diisi otomatis sesuai jenis pelanggaran yang dipilih wali kelas atau guru piket.</p>
    </section>
    <section class="panel"><div class="panel-heading"><h2>Daftar Jenis Pelanggaran</h2></div>
        @include('learnedc.components.table', ['headers' => ['Nama pelanggaran', 'Poin BK'], 'rows' => [['Terlambat masuk sekolah', '5'], ['Tidak memakai atribut lengkap', '3'], ['Meninggalkan kelas tanpa izin', '8']], 'actions' => true])
    </section>
@endsection
