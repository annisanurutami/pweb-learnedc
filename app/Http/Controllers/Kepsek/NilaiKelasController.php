<?php

namespace App\Http\Controllers\Kepsek;

use App\Http\Controllers\Controller;
use App\Services\LaporanSekolah;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class NilaiKelasController extends Controller
{
    /** B1: rata-rata nilai per kelas dan mata pelajaran, dengan filter semester. */
    public function index(Request $request, LaporanSekolah $laporan): View
    {
        $semester = $laporan->semesterTerpilih($request->integer('semester_id') ?: null);
        $baris = $semester ? $laporan->rataRataNilai($semester) : collect();

        return view('kepsek.nilai.index', [
            'daftarSemester' => $laporan->daftarSemester(),
            'semester' => $semester,
            'baris' => $baris,
            'perKelas' => $laporan->rataRataPerKelas($baris),
        ]);
    }

    /** B2: laporan PDF, isinya sama dengan halaman ditambah ringkasan keaktifan. */
    public function pdf(Request $request, LaporanSekolah $laporan): Response
    {
        $semester = $laporan->semesterTerpilih($request->integer('semester_id') ?: null);

        abort_if($semester === null, 404, 'Belum ada semester.');

        $baris = $laporan->rataRataNilai($semester);
        $keaktifan = $laporan->keaktifan($semester);

        $berkas = 'laporan-sekolah-' . Str::slug($semester->nama . ' ' . $semester->tahunAjaran?->nama) . '.pdf';

        return Pdf::loadView('kepsek.nilai.pdf', [
            'semester' => $semester,
            'baris' => $baris,
            'perKelas' => $laporan->rataRataPerKelas($baris),
            'ringkasan' => $keaktifan['ringkasan'],
            'guru' => $keaktifan['guru'],
        ])->setPaper('a4')->download($berkas);
    }
}