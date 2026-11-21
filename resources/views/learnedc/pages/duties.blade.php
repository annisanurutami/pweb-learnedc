@extends('learnedc.layouts.app')

@section('content')
    @if ($roleKey === 'admin')
        <section class="panel">
            <div class="panel-heading"><h2>Tambah Jadwal Piket Guru</h2></div>
            <form class="form-grid">
                <label class="field"><span>Guru</span><select><option>Pilih guru</option><option>Dewi Anggraini</option></select></label>
                <label class="field"><span>Hari</span><select><option>Pilih hari</option><option>Senin</option><option>Selasa</option></select></label>
                <label class="field"><span>Lokasi piket</span><input placeholder="Lokasi"></label>
                <button class="button primary" type="button">Simpan piket guru</button>
            </form>
        </section>
    @elseif ($roleKey === 'wali-kelas')
        <section class="panel"><div class="panel-heading"><h2>Jadwal Piket Guru Saya</h2></div>
            @include('learnedc.components.table', ['headers' => ['Guru', 'Hari', 'Lokasi'], 'rows' => [['Annisa Nurutami', 'Senin', 'Gerbang utama'], ['Dewi Anggraini', 'Rabu', 'Koridor lantai 2']]])
        </section>
        <section class="panel">
            <div class="panel-heading"><h2>Atur Jadwal Piket Kelas</h2></div>
            <form class="form-grid">
                <label class="field"><span>Siswa</span><select><option>Pilih siswa</option><option>Rafi Nugraha</option></select></label>
                <label class="field"><span>Hari piket</span><select><option>Pilih hari</option><option>Senin</option></select></label>
                <button class="button primary" type="button">Simpan piket kelas</button>
            </form>
        </section>
        <section class="panel"><div class="panel-heading"><h2>Jadwal Piket Siswa di Kelas Saya</h2></div>
            @include('learnedc.components.table', ['headers' => ['Kelas', 'Hari', 'Siswa'], 'rows' => [['XI RPL 1', 'Senin', 'Rafi Nugraha'], ['XI RPL 1', 'Selasa', 'Nadia Putri']], 'actions' => true])
        </section>
    @else
        <section class="panel"><div class="panel-heading"><h2>{{ $roleKey === 'guru-piket' ? 'Jadwal Piket Saya' : 'Jadwal Piket Guru' }}</h2></div>
            @include('learnedc.components.table', ['headers' => ['Guru', 'Hari', 'Lokasi'], 'rows' => [['Annisa Nurutami', 'Senin', 'Gerbang utama'], ['Dewi Anggraini', 'Rabu', 'Koridor lantai 2']], 'actions' => $roleKey === 'admin'])
        </section>
    @endif
@endsection
