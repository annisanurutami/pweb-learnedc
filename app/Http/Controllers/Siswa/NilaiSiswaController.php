<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Services\PerhitunganNilai;
use App\Support\KonteksSiswa;
use Illuminate\View\View;

class NilaiSiswaController extends Controller
{
    /** E1: nilai per mata pelajaran dan rata-rata keseluruhan pada semester aktif. */
    public function index(PerhitunganNilai $perhitungan): View
    {
        $konteks = KonteksSiswa::dari(auth()->user());

        $ringkasan = collect();

        if ($konteks->siap()) {
            $ringkasan = $perhitungan->ringkasanSiswa($konteks->siswa, $konteks->semester, $konteks->kelas);
        }

        return view('siswa.nilai.index', [
            'konteks' => $konteks,
            'ringkasan' => $ringkasan,
            'rataRata' => $perhitungan->rataRata($ringkasan),
            'bobot' => $konteks->semester ? $perhitungan->bobot($konteks->semester) : null,
        ]);
    }
}