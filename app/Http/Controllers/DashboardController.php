<?php

namespace App\Http\Controllers;

use App\Models\Guru;
use App\Models\GuruMapel;
use App\Models\JadwalPelajaran;
use App\Models\Kelas;
use App\Models\KelasSiswa;
use App\Models\MataPelajaran;
use App\Models\Semester;
use App\Models\Siswa;
use App\Models\Tugas;
use App\Services\PerhitunganNilai;
use App\Services\RekapKehadiran;
use App\Support\Angka;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /** /dashboard => arahkan ke dashboard sesuai role. */
    public function redirect(): RedirectResponse
    {
        return redirect()->route(auth()->user()->dashboardRouteName());
    }

    public function admin(): View
    {
        $semester = $this->semesterAktif();

        return $this->tampil('Dashboard Super Admin', 'PORTAL ADMINISTRATOR', $semester, [
            $this->stat('Siswa terdaftar', Siswa::count(), 'Data dalam sistem'),
            $this->stat('Guru terdaftar', Guru::count(), 'Data dalam sistem'),
            $this->stat('Kelas aktif', $this->jumlahKelas($semester), 'Tahun ajaran berjalan'),
            $this->stat('Mata pelajaran', MataPelajaran::count(), 'Data dalam sistem'),
        ]);
    }

    public function kepsek(): View
    {
        $semester = $this->semesterAktif();

        return $this->tampil('Dashboard Kepala Sekolah', 'PORTAL KEPALA SEKOLAH', $semester, [
            $this->stat('Siswa', Siswa::count(), 'Data dalam sistem'),
            $this->stat('Guru', Guru::count(), 'Data dalam sistem'),
            $this->stat('Kelas aktif', $this->jumlahKelas($semester), 'Tahun ajaran berjalan'),
        ]);
    }

    public function walikelas(): View
    {
        $semester = $this->semesterAktif();
        $guru = auth()->user()->guru;
        $kelasWali = $this->kelasWali($guru, $semester);

        return $this->tampil('Dashboard Wali Kelas', 'PORTAL WALI KELAS', $semester, [
            $this->stat('Kelas diwali', $kelasWali?->nama_kelas ?? '-', 'Tahun ajaran berjalan'),
            $this->stat(
                'Siswa di kelas',
                $kelasWali ? KelasSiswa::where('kelas_id', $kelasWali->id)->count() : 0,
                'Siswa terdaftar'
            ),
            ...$this->statMengajar($guru, $semester),
        ]);
    }

    public function guru(): View
    {
        $semester = $this->semesterAktif();

        return $this->tampil(
            'Dashboard Guru',
            'PORTAL GURU',
            $semester,
            $this->statMengajar(auth()->user()->guru, $semester)
        );
    }

    public function siswa(): View
    {
        $semester = $this->semesterAktif();
        $siswa = auth()->user()->siswa;
        $kelas = $this->kelasSiswa($siswa, $semester);

        $statistik = [
            $this->stat('Kelas', $kelas?->nama_kelas ?? '-', 'Tahun ajaran berjalan'),
        ];

        if ($siswa && $semester && $kelas) {
            $ringkasan = app(PerhitunganNilai::class)->ringkasanSiswa($siswa, $semester, $kelas);
            $rataRata = app(PerhitunganNilai::class)->rataRata($ringkasan);
            $kehadiran = app(RekapKehadiran::class)->siswa($siswa, $semester);

            $statistik[] = $this->stat(
                'Kehadiran',
                $kehadiran['persen'] !== null ? Angka::nilai($kehadiran['persen'], 1) . '%' : '-',
                $kehadiran['total'] . ' pertemuan tercatat'
            );
            $statistik[] = $this->stat('Rata-rata nilai', Angka::nilai($rataRata), 'Semester berjalan');
            $statistik[] = $this->stat('Tugas terbuka', $this->tugasTerbuka($siswa, $semester, $kelas), 'Belum dikumpulkan');
        }

        return $this->tampil('Dashboard Siswa', 'DASHBOARD SISWA', $semester, $statistik);
    }

    public function bendahara(): View
    {
        $semester = $this->semesterAktif();
        $kelas = $this->kelasSiswa(auth()->user()->siswa, $semester);

        return $this->tampil('Dashboard Bendahara', 'PORTAL BENDAHARA KELAS', $semester, [
            $this->stat('Kelas', $kelas?->nama_kelas ?? '-', 'Tahun ajaran berjalan'),
            $this->stat(
                'Siswa di kelas',
                $kelas ? KelasSiswa::where('kelas_id', $kelas->id)->count() : 0,
                'Siswa terdaftar'
            ),
        ]);
    }

    /**
     * @param  list<array{label: string, nilai: int|string, keterangan: string}>  $statistik
     */
    private function tampil(string $judul, string $eyebrow, ?Semester $semester, array $statistik): View
    {
        return view('dashboard', [
            'judul' => $judul,
            'eyebrow' => $eyebrow,
            'semester' => $semester,
            'statistik' => $statistik,
        ]);
    }

    /** @return array{label: string, nilai: int|string, keterangan: string} */
    private function stat(string $label, int|string $nilai, string $keterangan): array
    {
        return ['label' => $label, 'nilai' => $nilai, 'keterangan' => $keterangan];
    }

    private function semesterAktif(): ?Semester
    {
        return Semester::with('tahunAjaran')->where('is_active', true)->first();
    }

    private function jumlahKelas(?Semester $semester): int
    {
        return $semester ? Kelas::where('tahun_ajaran_id', $semester->tahun_ajaran_id)->count() : 0;
    }

    private function kelasWali(?Guru $guru, ?Semester $semester): ?Kelas
    {
        if (!$guru || !$semester) {
            return null;
        }

        return Kelas::where('wali_kelas_id', $guru->id)
            ->where('tahun_ajaran_id', $semester->tahun_ajaran_id)
            ->first();
    }

    private function kelasSiswa(?Siswa $siswa, ?Semester $semester): ?Kelas
    {
        if (!$siswa || !$semester) {
            return null;
        }

        return Kelas::where('tahun_ajaran_id', $semester->tahun_ajaran_id)
            ->whereIn('id', KelasSiswa::where('siswa_id', $siswa->id)->select('kelas_id'))
            ->first();
    }

    /** Tugas yang masih terbuka dan belum dikumpulkan siswa. */
    private function tugasTerbuka(Siswa $siswa, Semester $semester, Kelas $kelas): int
    {
        return Tugas::where('semester_id', $semester->id)
            ->where('deadline', '>', now())
            ->whereHas('guruMapel', fn($q) => $q->where('kelas_id', $kelas->id))
            ->whereDoesntHave('pengumpulan', fn($q) => $q
                ->where('siswa_id', $siswa->id)
                ->whereNotNull('submitted_at'))
            ->count();
    }

    /**
     * Ringkasan mengajar: jumlah kelas yang diampu dan jadwal hari ini (WIB).
     *
     * @return list<array{label: string, nilai: int|string, keterangan: string}>
     */
    private function statMengajar(?Guru $guru, ?Semester $semester): array
    {
        $diampu = 0;
        $hariIni = 0;

        if ($guru && $semester) {
            $diampu = GuruMapel::where('guru_id', $guru->id)
                ->whereHas('kelas', fn($q) => $q->where('tahun_ajaran_id', $semester->tahun_ajaran_id))
                ->count();

            $hariIni = JadwalPelajaran::where('semester_id', $semester->id)
                ->whereHas('guruMapel', fn($q) => $q->where('guru_id', $guru->id))
                ->whereHas('hari', fn($q) => $q->where('urutan', now()->dayOfWeekIso))
                ->count();
        }

        return [
            $this->stat('Kelas diampu', $diampu, 'Kelas dan mata pelajaran'),
            $this->stat('Jadwal hari ini', $hariIni, 'Pertemuan mengajar'),
        ];
    }
}