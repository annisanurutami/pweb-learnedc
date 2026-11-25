<?php

namespace App\Services;

use App\Models\BobotNilai;
use App\Models\GuruMapel;
use App\Models\Kelas;
use App\Models\Nilai;
use App\Models\Semester;
use App\Models\Siswa;
use App\Models\Tugas;
use Illuminate\Support\Collection;

/**
 * Rumus nilai akhir per mata pelajaran. Satu-satunya tempat rumus ini ditulis,
 * dipakai halaman nilai siswa, rapor, dan laporan kepala sekolah.
 *
 * Nilai akhir = rata-rata berbobot dari dua komponen:
 *   - tugas : rata-rata nilai jenis tugas_harian
 *   - ujian : rata-rata nilai jenis UH, UTS, dan UAS
 * Komponen yang belum punya nilai tidak ikut dihitung, bobotnya dinormalisasi.
 */
class PerhitunganNilai
{
    public const BOBOT_TUGAS_BAWAAN = 40;

    public const BOBOT_UJIAN_BAWAAN = 60;

    private const JENIS_UJIAN = ['UH', 'UTS', 'UAS'];

    /** @return array{tugas: int, ujian: int} */
    public function bobot(Semester $semester): array
    {
        $bobot = BobotNilai::where('semester_id', $semester->id)->first();

        return [
            'tugas' => (int) ($bobot?->bobot_tugas ?? self::BOBOT_TUGAS_BAWAAN),
            'ujian' => (int) ($bobot?->bobot_ujian ?? self::BOBOT_UJIAN_BAWAAN),
        ];
    }

    /**
     * @param  Collection<int, Nilai>  $nilai  semua nilai satu siswa pada satu mapel dan semester
     * @param  array{tugas: int, ujian: int}  $bobot
     * @return array{tugas: ?float, ujian: ?float, akhir: ?float}
     */
    public function hitung(Collection $nilai, array $bobot): array
    {
        $tugas = $nilai->where('jenis', 'tugas_harian')->map(fn ($n) => (float) $n->nilai)->avg();
        $ujian = $nilai->whereIn('jenis', self::JENIS_UJIAN)->map(fn ($n) => (float) $n->nilai)->avg();

        $komponen = [];

        if ($tugas !== null) {
            $komponen[] = [(float) $tugas, $bobot['tugas']];
        }

        if ($ujian !== null) {
            $komponen[] = [(float) $ujian, $bobot['ujian']];
        }

        $akhir = null;

        if ($komponen !== []) {
            $totalBobot = array_sum(array_column($komponen, 1));

            $akhir = $totalBobot > 0
                ? array_sum(array_map(fn ($k) => $k[0] * $k[1], $komponen)) / $totalBobot
                : array_sum(array_column($komponen, 0)) / count($komponen);
        }

        return [
            'tugas' => $tugas !== null ? round((float) $tugas, 2) : null,
            'ujian' => $ujian !== null ? round((float) $ujian, 2) : null,
            'akhir' => $akhir !== null ? round($akhir, 2) : null,
        ];
    }

    /**
     * Ringkasan nilai satu siswa untuk setiap mata pelajaran di kelasnya.
     *
     * @return Collection<int, array{mapel: string, guru: string, tugas: ?float, ujian: ?float, akhir: ?float, tugas_dinilai: int, total_tugas: int}>
     */
    public function ringkasanSiswa(Siswa $siswa, Semester $semester, Kelas $kelas): Collection
    {
        $penugasan = GuruMapel::with(['mapel', 'guru'])
            ->where('kelas_id', $kelas->id)
            ->get()
            ->sortBy(fn ($gm) => $gm->mapel->nama)
            ->values();

        $nilai = Nilai::where('siswa_id', $siswa->id)
            ->where('semester_id', $semester->id)
            ->get()
            ->groupBy('guru_mapel_id');

        $totalTugas = Tugas::where('semester_id', $semester->id)
            ->whereIn('guru_mapel_id', $penugasan->pluck('id'))
            ->get()
            ->groupBy('guru_mapel_id')
            ->map->count();

        $bobot = $this->bobot($semester);

        return $penugasan->map(function ($gm) use ($nilai, $totalTugas, $bobot) {
            $milikMapel = $nilai->get($gm->id, collect());

            return [
                'mapel' => $gm->mapel->nama,
                'guru' => $gm->guru->nama,
                'tugas_dinilai' => $milikMapel->where('jenis', 'tugas_harian')->count(),
                'total_tugas' => (int) $totalTugas->get($gm->id, 0),
            ] + $this->hitung($milikMapel, $bobot);
        });
    }

    /**
     * Rata-rata nilai akhir seluruh mata pelajaran yang sudah punya nilai.
     *
     * @param  Collection<int, array{akhir: ?float}>  $ringkasan
     */
    public function rataRata(Collection $ringkasan): ?float
    {
        $rata = $ringkasan->pluck('akhir')->filter(fn ($n) => $n !== null)->avg();

        return $rata !== null ? round((float) $rata, 2) : null;
    }
}