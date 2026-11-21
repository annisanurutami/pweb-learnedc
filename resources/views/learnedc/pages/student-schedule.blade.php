@extends('learnedc.layouts.app')

@section('content')
    <section class="panel"><div class="panel-heading"><h2>Jadwal Pelajaran Saya</h2></div>
        @include('learnedc.components.table', ['headers' => ['Hari', 'Mulai', 'Selesai', 'Mata pelajaran', 'Guru'], 'rows' => [['Senin', '07:30', '09:00', 'Pemrograman Web', 'Dewi Anggraini'], ['Selasa', '09:30', '11:00', 'Basis Data', 'Budi Santoso'], ['Rabu', '07:30', '09:00', 'Jaringan Komputer', 'Rina Lestari']]])
    </section>
@endsection
