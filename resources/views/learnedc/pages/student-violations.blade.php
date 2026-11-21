@extends('learnedc.layouts.app')

@section('content')
    <section class="panel"><div class="panel-heading"><h2>Catatan BK</h2></div>
        @include('learnedc.components.table', ['headers' => ['Tanggal', 'Catatan', 'Poin'], 'rows' => [['17 Nov 2025', 'Terlambat masuk sekolah', '5'], ['15 Nov 2025', 'Tidak memakai atribut lengkap', '3']]])
    </section>
@endsection
