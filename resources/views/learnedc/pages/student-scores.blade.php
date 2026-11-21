@extends('learnedc.layouts.app')

@section('content')
    <section class="panel"><div class="panel-heading"><h2>Rekap Nilai</h2></div>
        @include('learnedc.components.table', ['headers' => ['Mata pelajaran', 'Tugas', 'UTS', 'UAS'], 'rows' => [['Pemrograman Web', '88', '85', '90'], ['Basis Data', '91', '87', '92'], ['Jaringan Komputer', '84', '86', '89']]])
    </section>
@endsection
