<?php

namespace App\Http\Controllers\Kepsek;

use App\Http\Controllers\Controller;
use App\Services\LaporanSekolah;
use Illuminate\View\View;

class BerandaController extends Controller
{
    /** Halaman utama kepala sekolah: grafik keaktifan guru dan siswa. Hanya membaca data. */
    public function index(LaporanSekolah $laporan): View
    {
        $semester = $laporan->semesterTerpilih(null);

        return view('kepsek.beranda', [
            'semester' => $semester,
            'data' => $semester ? $laporan->keaktifan($semester) : null,
        ]);
    }
}