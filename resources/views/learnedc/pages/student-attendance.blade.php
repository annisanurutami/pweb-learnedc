@extends('learnedc.layouts.app')

@section('content')
    <section class="panel"><div class="panel-heading"><h2>Riwayat Presensi</h2></div>
        @include('learnedc.components.table', ['headers' => ['Tanggal', 'Mata pelajaran', 'Status'], 'rows' => [['17 Nov 2025', 'Pemrograman Web', 'Hadir'], ['17 Nov 2025', 'Basis Data', 'Hadir'], ['14 Nov 2025', 'Jaringan Komputer', 'Izin']]])
    </section>
@endsection
