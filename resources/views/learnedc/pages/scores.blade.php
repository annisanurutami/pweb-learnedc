@extends('learnedc.layouts.app')

@section('content')
    <section class="panel">
        <div class="panel-heading"><h2>Input Nilai Akademik</h2></div>
        <form class="form-grid">
            <label class="field"><span>{{ $roleKey === 'wali-kelas' ? 'Jadwal di kelas yang saya walikan' : 'Jadwal mengajar' }}</span><select><option>Pilih jadwal</option><option>XI RPL 1 · Pemrograman Web</option></select></label>
            <label class="field"><span>{{ $roleKey === 'wali-kelas' ? 'Siswa di kelas yang saya walikan' : 'Siswa di kelas yang diajar' }}</span><select><option>Pilih siswa</option><option>Rafi Nugraha</option></select></label>
            <label class="field"><span>Nilai tugas (0–100)</span><input type="number" min="0" max="100" step="0.01" placeholder="0–100"></label>
            <label class="field"><span>Nilai UTS (0–100)</span><input type="number" min="0" max="100" step="0.01" placeholder="0–100"></label>
            <label class="field"><span>Nilai UAS (0–100)</span><input type="number" min="0" max="100" step="0.01" placeholder="0–100"></label>
            <button class="button primary" type="button">Simpan nilai</button>
        </form>
    </section>
    <section class="panel"><div class="panel-heading"><h2>{{ $roleKey === 'wali-kelas' ? 'Rekap Nilai Kelas' : 'Rekap Nilai yang Dikelola' }}</h2></div>
        @include('learnedc.components.table', ['headers' => ['Siswa', 'Mata pelajaran', 'Tugas', 'UTS', 'UAS'], 'rows' => [['Rafi Nugraha', 'Pemrograman Web', '88', '85', '90'], ['Nadia Putri', 'Pemrograman Web', '92', '89', '94']], 'actions' => true])
    </section>
@endsection
