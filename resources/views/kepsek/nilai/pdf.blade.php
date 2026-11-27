<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <title>Laporan Sekolah</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #2b1f33;
        }

        h1 {
            margin: 0;
            font-size: 18px;
            color: #5b2c7a;
        }

        h2 {
            margin: 18px 0 6px;
            font-size: 13px;
            color: #5b2c7a;
        }

        p {
            margin: 2px 0;
        }

        .muted {
            color: #7a6a85;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 4px;
        }

        th {
            background: #f1e4f7;
            color: #5b2c7a;
            text-align: left;
            font-size: 10px;
            text-transform: uppercase;
        }

        th,
        td {
            padding: 6px 8px;
            border: 1px solid #ddcfe6;
        }

        .angka {
            text-align: right;
        }
    </style>
</head>

<body>
    <h1>Laporan Sekolah</h1>
    <p class="muted">Semester {{ $semester->nama }} {{ $semester->tahunAjaran?->nama }} &middot; dicetak
        {{ now()->format('d-m-Y H:i') }} WIB</p>

    <h2>Rata-rata nilai per kelas</h2>
    <table>
        <thead>
            <tr>
                <th>Kelas</th>
                <th class="angka">Rata-rata</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($perKelas as $k)
                <tr>
                    <td>{{ $k['kelas'] }}</td>
                    <td class="angka">{{ \App\Support\Angka::nilai($k['rata_rata']) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="2">Belum ada data.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <h2>Rata-rata nilai per kelas dan mata pelajaran</h2>
    <table>
        <thead>
            <tr>
                <th>Kelas</th>
                <th>Mata pelajaran</th>
                <th>Guru</th>
                <th class="angka">Dinilai</th>
                <th class="angka">Rata-rata</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($baris as $b)
                <tr>
                    <td>{{ $b['kelas'] }}</td>
                    <td>{{ $b['mapel'] }}</td>
                    <td>{{ $b['guru'] }}</td>
                    <td class="angka">{{ $b['jumlah_dinilai'] }} / {{ $b['jumlah_siswa'] }}</td>
                    <td class="angka">{{ \App\Support\Angka::nilai($b['rata_rata']) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5">Belum ada data.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <h2>Ringkasan keaktifan</h2>
    <table>
        <tbody>
            <tr>
                <td>Siswa aktif (login 7 hari terakhir)</td>
                <td class="angka">{{ $ringkasan['siswa_aktif'] }} / {{ $ringkasan['total_siswa'] }}</td>
            </tr>
            <tr>
                <td>Guru aktif (login 7 hari terakhir)</td>
                <td class="angka">{{ $ringkasan['guru_aktif'] }} / {{ $ringkasan['total_guru'] }}</td>
            </tr>
            <tr>
                <td>Tugas dibuat</td>
                <td class="angka">{{ $ringkasan['tugas_dibuat'] }}</td>
            </tr>
            <tr>
                <td>Materi diunggah</td>
                <td class="angka">{{ $ringkasan['materi_dibuat'] }}</td>
            </tr>
            <tr>
                <td>Pengumpulan tugas</td>
                <td class="angka">{{ $ringkasan['pengumpulan'] }}</td>
            </tr>
            <tr>
                <td>Kehadiran siswa rata-rata</td>
                <td class="angka">
                    {{ $ringkasan['kehadiran'] !== null ? \App\Support\Angka::nilai($ringkasan['kehadiran'], 1) . '%' : '-' }}
                </td>
            </tr>
        </tbody>
    </table>

    <h2>Aktivitas guru</h2>
    <table>
        <thead>
            <tr>
                <th>Guru</th>
                <th class="angka">Materi</th>
                <th class="angka">Tugas</th>
                <th class="angka">Pertemuan diabsen</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($guru as $g)
                <tr>
                    <td>{{ $g['nama'] }}</td>
                    <td class="angka">{{ $g['materi'] }}</td>
                    <td class="angka">{{ $g['tugas'] }}</td>
                    <td class="angka">{{ $g['absensi'] }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4">Belum ada data guru.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>

</html>