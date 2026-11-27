<?php

namespace App\Services;

use App\Models\Guru;
use App\Models\GuruMapel;
use App\Models\KelasSiswa;
use App\Models\LogLogin;
use App\Models\Materi;
use App\Models\Nilai;
use App\Models\PengumpulanTugas;
use App\Models\PresensiSiswa;
use App\Models\Semester;
use App\Models\Siswa;
use App\Models\Tugas;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Data laporan tingkat sekolah untuk kepala sekolah: rata-rata nilai dan keaktifan guru dan siswa.
 * Semua hanya membaca data.
 */
class LaporanSekolah
{
    public function __construct(private PerhitunganNilai $perhitungan)
    {
    }

    /** @return Collection<int, Semester> */
    public function daftarSemester(): Collection
    {
        return Semester::with('tahunAjaran')->orderByDesc('tahun_ajaran_id')->orderByDesc('id')->get();
    }

    /** Semester yang dipilih; bila tidak valid, semester aktif; bila tidak ada, semester terbaru. */
    public function semesterTerpilih(?int $id): ?Semester
    {
        $daftar = $this->daftarSemester();

        return $daftar->firstWhere('id', $id)
            ?? $daftar->firstWhere('is_active', true)
            ?? $daftar->first();
    }

    /**
     * Rata-rata nilai akhir untuk setiap kelas dan mata pelajaran pada satu semester.
     *
     * @return Collection<int, array{kelas: string, mapel: string, guru: string, jumlah_siswa: int, jumlah_dinilai: int, rata_rata: ?float}>
     */
    public function rataRataNilai(Semester $semester): Collection
    {
        $penugasan = GuruMapel::with(['kelas', 'mapel', 'guru'])
            ->whereHas('kelas', fn($q) => $q->where('tahun_ajaran_id', $semester->tahun_ajaran_id))
            ->get()
            ->sortBy(fn($gm) => $gm->kelas->nama_kelas . '|' . $gm->mapel->nama)
            ->values();

        $nilai = Nilai::where('semester_id', $semester->id)->get()->groupBy('guru_mapel_id');

        $jumlahSiswa = KelasSiswa::whereIn('kelas_id', $penugasan->pluck('kelas_id')->unique())
            ->get()
            ->groupBy('kelas_id')
            ->map->count();

        $bobot = $this->perhitungan->bobot($semester);

        return $penugasan->map(function ($gm) use ($nilai, $jumlahSiswa, $bobot) {
            $akhir = $nilai->get($gm->id, collect())
                ->groupBy('siswa_id')
                ->map(fn($milikSiswa) => $this->perhitungan->hitung($milikSiswa, $bobot)['akhir'])
                ->filter(fn($a) => $a !== null);

            return [
                'kelas' => $gm->kelas->nama_kelas,
                'mapel' => $gm->mapel->nama,
                'guru' => $gm->guru->nama,
                'jumlah_siswa' => (int) $jumlahSiswa->get($gm->kelas_id, 0),
                'jumlah_dinilai' => $akhir->count(),
                'rata_rata' => $akhir->isNotEmpty() ? round((float) $akhir->avg(), 2) : null,
            ];
        });
    }

    /**
     * Rata-rata per kelas: rata-rata dari rata-rata mata pelajaran yang sudah punya nilai.
     *
     * @param  Collection<int, array{kelas: string, rata_rata: ?float}>  $baris
     * @return Collection<int, array{kelas: string, rata_rata: ?float}>
     */
    public function rataRataPerKelas(Collection $baris): Collection
    {
        return $baris->groupBy('kelas')->map(function ($kelompok, $kelas) {
            $rata = $kelompok->pluck('rata_rata')->filter(fn($r) => $r !== null)->avg();

            return ['kelas' => (string) $kelas, 'rata_rata' => $rata !== null ? round((float) $rata, 2) : null];
        })->values();
    }

    /**
     * Keaktifan guru dan siswa: login, materi dan tugas yang dibuat, absensi yang diinput,
     * serta pengumpulan tugas.
     *
     * @return array<string, mixed>
     */
    public function keaktifan(Semester $semester, int $hari = 14): array
    {
        $mulai = now()->subDays($hari - 1)->startOfDay();
        $tanggal = collect(range(0, $hari - 1))->map(fn($i) => $mulai->copy()->addDays($i));

        $idGuru = User::role('guru')->pluck('id');
        $idSiswa = User::role('siswa')->pluck('id');

        $log = LogLogin::where('logged_at', '>=', $mulai)->get(['user_id', 'logged_at']);

        $loginPerHari = fn(Collection $ids) => $tanggal->map(
            fn($t) => $log->filter(fn($l) => $ids->contains($l->user_id) && $l->logged_at->isSameDay($t))->count()
        )->all();

        $pengumpulan = PengumpulanTugas::whereNotNull('submitted_at')
            ->where('submitted_at', '>=', $mulai)
            ->get(['submitted_at']);

        $tujuhHari = now()->subDays(6)->startOfDay();
        $aktif = fn(Collection $ids) => LogLogin::where('logged_at', '>=', $tujuhHari)
            ->whereIn('user_id', $ids)
            ->distinct()
            ->count('user_id');

        $presensi = PresensiSiswa::whereDate('tanggal', '>=', $semester->tgl_mulai->toDateString())
            ->whereDate('tanggal', '<=', $semester->tgl_selesai->toDateString())
            ->get(['status']);

        return [
            'grafik' => [
                'tanggal' => $tanggal->map(fn($t) => $t->format('d/m'))->all(),
                'login_guru' => $loginPerHari($idGuru),
                'login_siswa' => $loginPerHari($idSiswa),
                'pengumpulan' => $tanggal->map(
                    fn($t) => $pengumpulan->filter(fn($p) => $p->submitted_at->isSameDay($t))->count()
                )->all(),
            ],
            'guru' => $this->aktivitasGuru($semester),
            'ringkasan' => [
                'siswa_aktif' => $aktif($idSiswa),
                'total_siswa' => Siswa::count(),
                'guru_aktif' => $aktif($idGuru),
                'total_guru' => Guru::count(),
                'tugas_dibuat' => Tugas::where('semester_id', $semester->id)->count(),
                'materi_dibuat' => Materi::where('semester_id', $semester->id)->count(),
                'pengumpulan' => PengumpulanTugas::whereNotNull('submitted_at')
                    ->whereHas('tugas', fn($q) => $q->where('semester_id', $semester->id))
                    ->count(),
                'kehadiran' => $presensi->isNotEmpty()
                    ? round($presensi->where('status', 'H')->count() / $presensi->count() * 100, 1)
                    : null,
            ],
        ];
    }

    /**
     * Jumlah materi, tugas, dan pertemuan yang diabsen oleh tiap guru pada satu semester.
     *
     * @return Collection<int, array{nama: string, materi: int, tugas: int, absensi: int, total: int}>
     */
    private function aktivitasGuru(Semester $semester): Collection
    {
        $penugasan = GuruMapel::get(['id', 'guru_id'])->groupBy('guru_id');

        $hitung = fn($model) => $model::where('semester_id', $semester->id)->get(['guru_mapel_id'])->groupBy('guru_mapel_id')->map->count();
        $materi = $hitung(Materi::class);
        $tugas = $hitung(Tugas::class);

        $absensi = PresensiSiswa::whereNotNull('jadwal_id')
            ->whereDate('tanggal', '>=', $semester->tgl_mulai->toDateString())
            ->whereDate('tanggal', '<=', $semester->tgl_selesai->toDateString())
            ->get(['jadwal_id', 'tanggal', 'dicatat_oleh'])
            ->groupBy('dicatat_oleh')
            ->map(fn($baris) => $baris->unique(fn($p) => $p->jadwal_id . '|' . $p->tanggal->format('Y-m-d'))->count());

        return Guru::get()->map(function ($guru) use ($penugasan, $materi, $tugas, $absensi) {
            $idPenugasan = $penugasan->get($guru->id, collect())->pluck('id');

            $jumlahMateri = $idPenugasan->sum(fn($id) => $materi->get($id, 0));
            $jumlahTugas = $idPenugasan->sum(fn($id) => $tugas->get($id, 0));
            $jumlahAbsensi = (int) $absensi->get($guru->user_id, 0);

            return [
                'nama' => $guru->nama,
                'materi' => (int) $jumlahMateri,
                'tugas' => (int) $jumlahTugas,
                'absensi' => $jumlahAbsensi,
                'total' => (int) ($jumlahMateri + $jumlahTugas + $jumlahAbsensi),
            ];
        })->sortByDesc('total')->values();
    }
}