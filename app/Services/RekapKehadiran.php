<?php

namespace App\Services;

use App\Models\JadwalPelajaran;
use App\Models\PresensiSiswa;
use App\Models\Semester;
use App\Models\Siswa;
use Illuminate\Support\Collection;

class RekapKehadiran
{
    /**
     * Rekap kehadiran seorang siswa pada rentang tanggal semester.
     * Persentase hadir = jumlah Hadir dibagi seluruh pertemuan yang tercatat.
     *
     * @return array{baris: Collection<int, PresensiSiswa>, jadwal: Collection<int, JadwalPelajaran>, jumlah: array<string, int>, total: int, persen: ?float}
     */
    public function siswa(Siswa $siswa, Semester $semester): array
    {
        $baris = PresensiSiswa::where('siswa_id', $siswa->id)
            ->whereDate('tanggal', '>=', $semester->tgl_mulai->toDateString())
            ->whereDate('tanggal', '<=', $semester->tgl_selesai->toDateString())
            ->orderByDesc('tanggal')
            ->orderByDesc('id')
            ->get();

        $jadwal = JadwalPelajaran::with('guruMapel.mapel')
            ->whereIn('id', $baris->pluck('jadwal_id')->filter()->unique())
            ->get()
            ->keyBy('id');

        $jumlah = [];

        foreach (['H', 'I', 'S', 'A'] as $kode) {
            $jumlah[$kode] = $baris->where('status', $kode)->count();
        }

        $total = $baris->count();

        return [
            'baris' => $baris,
            'jadwal' => $jadwal,
            'jumlah' => $jumlah,
            'total' => $total,
            'persen' => $total > 0 ? round($jumlah['H'] / $total * 100, 1) : null,
        ];
    }
}