<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\GuruMapel;
use App\Models\JadwalPelajaran;
use App\Models\KelasSiswa;
use App\Models\PresensiSiswa;
use App\Models\Semester;
use App\Models\Siswa;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AbsensiSiswaController extends Controller
{
    /** Kode status pada kolom presensi_siswa.status. */
    public const STATUS = ['H' => 'Hadir', 'I' => 'Izin', 'S' => 'Sakit', 'A' => 'Alpa'];

    /** C5: pilih pertemuan (jadwal dan tanggal), isi kehadiran, dan lihat rekap. */
    public function index(Request $request, GuruMapel $penugasan): View
    {
        Gate::authorize('kelola', $penugasan);

        $semester = $this->semesterAktif();
        $penugasan->load(['kelas', 'mapel']);

        $daftarJadwal = $semester ? $this->jadwalSemester($penugasan, $semester) : collect();

        $jadwal = null;
        $tanggal = null;
        $galat = null;
        $siap = false;
        $daftarSiswa = collect();
        $presensi = collect();

        if ($semester && $request->filled('jadwal_id') && $request->filled('tanggal')) {
            $jadwal = $daftarJadwal->firstWhere('id', $request->integer('jadwal_id'));
            $tanggal = (string) $request->query('tanggal');

            $galat = $jadwal
                ? $this->cekPertemuan($semester, $jadwal, $tanggal)
                : 'Jadwal tidak ditemukan pada kelas ini.';

            if ($galat === null) {
                $siap = true;
                $daftarSiswa = $this->siswaKelas($penugasan);
                $presensi = PresensiSiswa::where('jadwal_id', $jadwal->id)
                    ->whereDate('tanggal', $tanggal)
                    ->get()
                    ->keyBy('siswa_id');
            }
        }

        return view('guru.absensi.index', [
            'penugasan' => $penugasan,
            'semester' => $semester,
            'daftarJadwal' => $daftarJadwal,
            'jadwal' => $jadwal,
            'tanggal' => $tanggal,
            'galat' => $galat,
            'siap' => $siap,
            'daftarSiswa' => $daftarSiswa,
            'presensi' => $presensi,
            'daftarStatus' => self::STATUS,
            'rekap' => $this->rekap($daftarJadwal),
        ]);
    }

    /** Menyimpan kehadiran seluruh siswa untuk satu pertemuan. */
    public function update(Request $request, GuruMapel $penugasan): RedirectResponse
    {
        Gate::authorize('kelola', $penugasan);

        $semester = $this->semesterAktif();

        if (! $semester) {
            return redirect()->route('guru.absensi.index', $penugasan)
                ->with('error', 'Belum ada semester aktif. Hubungi administrator.');
        }

        $data = $request->validate([
            'jadwal_id' => ['required', 'integer'],
            'tanggal' => ['required', 'date_format:Y-m-d'],
            'status' => ['required', 'array'],
            'status.*' => ['required', Rule::in(array_keys(self::STATUS))],
            'keterangan' => ['nullable', 'array'],
            'keterangan.*' => ['nullable', 'string', 'max:255'],
        ], $this->pesan());

        $jadwal = $this->jadwalSemester($penugasan, $semester)->firstWhere('id', (int) $data['jadwal_id']);

        if (! $jadwal) {
            throw ValidationException::withMessages([
                'jadwal_id' => 'Jadwal tidak ditemukan pada kelas ini.',
            ]);
        }

        if ($galat = $this->cekPertemuan($semester, $jadwal, $data['tanggal'])) {
            throw ValidationException::withMessages(['tanggal' => $galat]);
        }

        // Semua siswa kelas harus ada di form, dan tidak boleh ada siswa dari kelas lain.
        $idKelas = $this->siswaKelas($penugasan)->pluck('id')->map(fn ($id) => (int) $id)->sort()->values()->all();
        $idDikirim = collect(array_keys($data['status']))->map(fn ($id) => (int) $id)->sort()->values()->all();

        if ($idKelas !== $idDikirim) {
            throw ValidationException::withMessages([
                'status' => 'Status kehadiran harus diisi untuk semua siswa di kelas ini.',
            ]);
        }

        DB::transaction(function () use ($data, $penugasan, $jadwal, $request) {
            foreach ($data['status'] as $siswaId => $status) {
                $baris = PresensiSiswa::where('siswa_id', $siswaId)
                    ->where('jadwal_id', $jadwal->id)
                    ->whereDate('tanggal', $data['tanggal'])
                    ->first()
                    ?? new PresensiSiswa([
                        'siswa_id' => $siswaId,
                        'kelas_id' => $penugasan->kelas_id,
                        'jadwal_id' => $jadwal->id,
                        'tanggal' => $data['tanggal'],
                    ]);

                $baris->status = $status;
                $baris->keterangan = $status === 'H' ? null : ($data['keterangan'][$siswaId] ?? null);
                $baris->dicatat_oleh = $request->user()->id;
                $baris->save();
            }
        });

        return redirect()
            ->route('guru.absensi.index', [
                'penugasan' => $penugasan,
                'jadwal_id' => $jadwal->id,
                'tanggal' => $data['tanggal'],
            ])
            ->with('success', 'Absensi tersimpan untuk '.count($data['status']).' siswa.');
    }

    /**
     * Pertemuan sah jika tanggalnya valid, tidak di masa depan, berada dalam semester aktif,
     * dan jatuh pada hari yang sama dengan hari jadwal. Mengembalikan pesan galat atau null.
     */
    private function cekPertemuan(Semester $semester, JadwalPelajaran $jadwal, string $tanggal): ?string
    {
        try {
            $tgl = Carbon::createFromFormat('!Y-m-d', $tanggal, config('app.timezone'));
        } catch (\Throwable) {
            $tgl = false;
        }

        // Format ulang untuk menolak tanggal yang tergulung, misalnya 2026-02-31.
        if (! $tgl || $tgl->format('Y-m-d') !== $tanggal) {
            return 'Format tanggal tidak valid.';
        }

        if ($tgl->gt(Carbon::today())) {
            return 'Tanggal absensi tidak boleh di masa depan.';
        }

        if ($tgl->lt($semester->tgl_mulai->copy()->startOfDay()) || $tgl->gt($semester->tgl_selesai->copy()->startOfDay())) {
            return 'Tanggal berada di luar rentang semester aktif.';
        }

        if ($tgl->dayOfWeekIso !== (int) $jadwal->hari->urutan) {
            return 'Tanggal tidak sesuai dengan hari jadwal ('.$jadwal->hari->nama_hari.').';
        }

        return null;
    }

    /** Rekap 15 pertemuan terakhir yang sudah diabsen. */
    private function rekap(Collection $daftarJadwal): Collection
    {
        if ($daftarJadwal->isEmpty()) {
            return collect();
        }

        return PresensiSiswa::whereIn('jadwal_id', $daftarJadwal->pluck('id'))
            ->get()
            ->groupBy(fn ($p) => $p->jadwal_id.'|'.$p->tanggal->format('Y-m-d'))
            ->map(function ($baris) use ($daftarJadwal) {
                $pertama = $baris->first();

                return [
                    'jadwal' => $daftarJadwal->firstWhere('id', $pertama->jadwal_id),
                    'tanggal' => $pertama->tanggal,
                    'jumlah' => collect(self::STATUS)
                        ->map(fn ($label, $kode) => $baris->where('status', $kode)->count())
                        ->all(),
                ];
            })
            ->sortByDesc(fn ($r) => $r['tanggal']->timestamp)
            ->take(15)
            ->values();
    }

    private function jadwalSemester(GuruMapel $penugasan, Semester $semester): Collection
    {
        return $penugasan->jadwalPelajaran()
            ->with('hari')
            ->where('semester_id', $semester->id)
            ->get()
            ->sortBy([
                fn ($a, $b) => $a->hari->urutan <=> $b->hari->urutan,
                fn ($a, $b) => $a->jam_mulai <=> $b->jam_mulai,
            ])
            ->values();
    }

    /** @return EloquentCollection<int, Siswa> */
    private function siswaKelas(GuruMapel $penugasan): EloquentCollection
    {
        return Siswa::whereIn(
            'id',
            KelasSiswa::where('kelas_id', $penugasan->kelas_id)->select('siswa_id')
        )->orderBy('nama')->get();
    }

    private function semesterAktif(): ?Semester
    {
        return Semester::where('is_active', true)->first();
    }

    /** @return array<string, string> */
    private function pesan(): array
    {
        return [
            'jadwal_id.required' => 'Pilih jadwal pertemuan.',
            'tanggal.required' => 'Tanggal wajib diisi.',
            'tanggal.date_format' => 'Format tanggal tidak valid.',
            'status.required' => 'Status kehadiran belum diisi.',
            'status.*.required' => 'Status kehadiran belum diisi.',
            'status.*.in' => 'Status kehadiran tidak valid.',
            'keterangan.*.max' => 'Keterangan maksimal 255 karakter.',
        ];
    }
}
