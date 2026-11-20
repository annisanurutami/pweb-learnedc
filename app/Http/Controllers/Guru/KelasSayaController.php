<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use App\Models\GuruMapel;
use App\Models\Semester;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class KelasSayaController extends Controller
{
    /** C1: daftar kelas dan mapel yang diampu guru di semester aktif. */
    public function index(): View
    {
        $guru = $this->guruLogin();
        $semester = $this->semesterAktif();
        $daftar = collect();

        if ($semester) {
            $daftar = GuruMapel::with([
                    'kelas',
                    'mapel',
                    'jadwalPelajaran' => fn ($q) => $q->where('semester_id', $semester->id)->with('hari'),
                ])
                ->where('guru_id', $guru->id)
                ->whereHas('kelas', fn ($q) => $q->where('tahun_ajaran_id', $semester->tahun_ajaran_id))
                ->get()
                ->sortBy(fn ($g) => $g->kelas->nama_kelas.' '.$g->mapel->nama)
                ->values();

            $daftar->each(fn ($g) => $g->setRelation('jadwalPelajaran', $this->urutkan($g->jadwalPelajaran)));
        }

        return view('guru.kelas.index', [
            'semester' => $semester,
            'daftar' => $daftar,
        ]);
    }

    /** Satu kelas dan mapel terpilih: konteks kerja untuk fitur guru lainnya. */
    public function show(GuruMapel $penugasan): View
    {
        Gate::authorize('kelola', $penugasan);

        $semester = $this->semesterAktif();
        $penugasan->load(['kelas.tahunAjaran', 'kelas.waliKelas', 'mapel']);

        $jadwal = $semester
            ? $this->urutkan(
                $penugasan->jadwalPelajaran()->with('hari')->where('semester_id', $semester->id)->get()
            )
            : collect();

        return view('guru.kelas.show', [
            'penugasan' => $penugasan,
            'semester' => $semester,
            'jadwal' => $jadwal,
            'jumlahSiswa' => $penugasan->kelas->kelasSiswa()->count(),
            'jumlahMateri' => $semester
                ? $penugasan->materi()->where('semester_id', $semester->id)->count()
                : 0,
            'jumlahTugas' => $semester
                ? $penugasan->tugas()->where('semester_id', $semester->id)->count()
                : 0,
        ]);
    }

    private function guruLogin(): Guru
    {
        $guru = auth()->user()->guru;

        abort_if($guru === null, 403, 'Akun ini belum terhubung ke data guru. Hubungi administrator.');

        return $guru;
    }

    private function semesterAktif(): ?Semester
    {
        return Semester::with('tahunAjaran')->where('is_active', true)->first();
    }

    /** Urut menurut hari (Senin dulu), lalu jam mulai. */
    private function urutkan(Collection $jadwal): Collection
    {
        return $jadwal
            ->sortBy([
                fn ($a, $b) => $a->hari->urutan <=> $b->hari->urutan,
                fn ($a, $b) => $a->jam_mulai <=> $b->jam_mulai,
            ])
            ->values();
    }
}