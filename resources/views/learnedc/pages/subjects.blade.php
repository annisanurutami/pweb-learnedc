@extends('learnedc.layouts.app')

@section('content')
    <section class="panel module-entry-panel">
        <div class="panel-heading"><div><span class="eyebrow">MASTER DATA</span><h2>Tambah Mata Pelajaran</h2></div></div>
        <form class="form-grid">
            <label class="field"><span>Kode mata pelajaran</span><input placeholder="Contoh: RPL-01"></label>
            <label class="field"><span>Nama mata pelajaran</span><input placeholder="Nama mata pelajaran"></label>
            <label class="field"><span>Tingkat kelas</span><select><option>Pilih tingkat</option><option>XI</option></select></label>
            <label class="field"><span>Jurusan</span><select><option>Pilih jurusan</option><option>Rekayasa Perangkat Lunak</option></select></label>
            <button class="button primary" type="button">Simpan mata pelajaran</button>
        </form>
    </section>
    <section class="panel">
        <div class="panel-heading"><div><span class="eyebrow">AKADEMIK</span><h2>Daftar Mata Pelajaran</h2></div></div>
        @include('learnedc.components.table', ['headers' => ['Kode', 'Mata pelajaran', 'Tingkat', 'Jurusan'], 'rows' => [['RPL-01', 'Pemrograman Web', 'X, XI, XII', 'Rekayasa Perangkat Lunak'], ['BD-02', 'Basis Data', 'XI, XII', 'Rekayasa Perangkat Lunak']], 'actions' => true])
    </section>
@endsection
