@extends('learnedc.layouts.app')

@section('content')
    <section class="panel"><div class="panel-heading"><h2>Prestasi Saya</h2></div>
        @include('learnedc.components.table', ['headers' => ['Prestasi', 'Tingkat', 'Tanggal'], 'rows' => [['Juara 1 Lomba Pemrograman', 'Kota', '12 Nov 2025'], ['Juara 2 Desain Poster', 'Sekolah', '5 Nov 2025']]])
    </section>
@endsection
