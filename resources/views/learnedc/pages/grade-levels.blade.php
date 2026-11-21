@extends('learnedc.layouts.app')

@section('content')
    <section class="panel">
        <div class="panel-heading"><div><span class="eyebrow">MASTER DATA</span><h2>Tambah Tingkat Kelas</h2></div></div>
        <form class="form-grid">
            <label class="field"><span>Nama tingkat</span><input placeholder="Contoh: X"></label>
            <label class="field"><span>Urutan</span><input type="number" placeholder="1"></label>
            <button class="button primary" type="button">Simpan tingkat kelas</button>
        </form>
    </section>
    <section class="panel">
        <div class="panel-heading"><h2>Daftar Tingkat Kelas</h2></div>
        @include('learnedc.components.table', ['headers' => ['Urutan', 'Nama tingkat'], 'rows' => [['1', 'X'], ['2', 'XI'], ['3', 'XII']], 'actions' => true])
    </section>
@endsection
