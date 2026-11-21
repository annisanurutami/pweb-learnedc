@extends('learnedc.layouts.app')

@section('content')
    <section class="panel">
        <div class="panel-heading"><h2>Input Presensi Harian</h2></div>
        <form class="form-grid">
            <label class="field"><span>{{ $roleKey === 'wali-kelas' ? 'Jadwal di kelas yang saya walikan' : 'Jadwal mengajar' }}</span><select><option>Pilih jadwal</option><option>XI RPL 1 · Pemrograman Web · Senin 07:30</option></select></label>
            <label class="field"><span>{{ $roleKey === 'wali-kelas' ? 'Siswa di kelas yang saya walikan' : 'Siswa di kelas yang diajar' }}</span><select><option>Pilih siswa</option><option>Rafi Nugraha</option></select></label>
            <label class="field"><span>Tanggal</span><input type="date" value="2025-11-17"></label>
            <label class="field"><span>Status</span><select><option>Pilih status</option><option>Hadir</option><option>Izin</option><option>Sakit</option><option>Alpha</option></select></label>
            <button class="button primary" type="button">Simpan presensi</button>
        </form>
        <p class="form-note">{{ $roleKey === 'wali-kelas' ? 'Daftar jadwal dan siswa dibatasi pada kelas yang Anda walikan.' : 'Daftar siswa divalidasi terhadap kelas pada jadwal mengajar yang dipilih.' }}</p>
    </section>
    <section class="panel"><div class="panel-heading"><h2>Riwayat Presensi</h2></div>
        @include('learnedc.components.table', ['headers' => ['Tanggal', 'Siswa', 'Mata pelajaran', 'Status'], 'rows' => [['17 Nov 2025', 'Rafi Nugraha', 'Pemrograman Web', 'Hadir'], ['17 Nov 2025', 'Nadia Putri', 'Pemrograman Web', 'Izin'], ['14 Nov 2025', 'Rafi Nugraha', 'Basis Data', 'Hadir']], 'actions' => true])
    </section>
@endsection
