@extends('learnedc.layouts.app')

@section('content')
    <section class="panel"><div class="panel-heading"><h2>Jadwal Piket Saya</h2></div>
        @include('learnedc.components.table', ['headers' => ['Hari'], 'rows' => [['Senin']]])
    </section>
    <section class="panel"><div class="panel-heading"><h2>Jadwal Piket Kelas</h2></div>
        @include('learnedc.components.table', ['headers' => ['Hari', 'Siswa'], 'rows' => [['Senin', 'Rafi Nugraha'], ['Selasa', 'Nadia Putri'], ['Rabu', 'Annisa Nurutami']]])
    </section>
@endsection
