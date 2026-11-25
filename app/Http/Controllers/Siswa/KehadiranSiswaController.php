<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Services\RekapKehadiran;
use App\Support\KonteksSiswa;
use Illuminate\View\View;

class KehadiranSiswaController extends Controller
{
    private const STATUS = ['H' => 'Hadir', 'I' => 'Izin', 'S' => 'Sakit', 'A' => 'Alpa'];

    /** E1: rekap dan riwayat kehadiran siswa pada semester aktif. */
    public function index(RekapKehadiran $rekap): View
    {
        $konteks = KonteksSiswa::dari(auth()->user());

        return view('siswa.kehadiran.index', [
            'konteks' => $konteks,
            'rekap' => $konteks->semester ? $rekap->siswa($konteks->siswa, $konteks->semester) : null,
            'daftarStatus' => self::STATUS,
        ]);
    }
}