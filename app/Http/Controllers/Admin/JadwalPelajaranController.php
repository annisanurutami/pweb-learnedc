<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GuruMapel;
use App\Models\Hari;
use App\Models\JadwalPelajaran;
use App\Models\Kelas;
use App\Models\Semester;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class JadwalPelajaranController extends Controller
{
    public function index(Request $request): View
    {
        $semester = $this->semesterAktif();
        $kelasId = $request->integer('kelas_id') ?: null;

        $daftar = collect();
        $daftarKelas = collect();

        if ($semester) {
            $daftar = JadwalPelajaran::query()
                ->join('hari', 'hari.id', '=', 'jadwal_pelajaran.hari_id')
                ->where('jadwal_pelajaran.semester_id', $semester->id)
                ->when($kelasId, fn ($q) => $q->whereHas(
                    'guruMapel',
                    fn ($g) => $g->where('kelas_id', $kelasId)
                ))
                ->with(['guruMapel.guru', 'guruMapel.mapel', 'guruMapel.kelas', 'hari'])
                ->orderBy('hari.urutan')
                ->orderBy('jadwal_pelajaran.jam_mulai')
                ->select('jadwal_pelajaran.*')
                ->get();

            $daftarKelas = Kelas::where('tahun_ajaran_id', $semester->tahun_ajaran_id)
                ->orderBy('nama_kelas')
                ->get();
        }

        return view('admin.jadwal.index', [
            'semester' => $semester,
            'daftar' => $daftar,
            'daftarKelas' => $daftarKelas,
            'kelasId' => $kelasId,
        ]);
    }

    public function create(Request $request): View|RedirectResponse
    {
        $semester = $this->semesterAktif();

        if (! $semester) {
            return $this->tanpaSemester();
        }

        $jadwal = new JadwalPelajaran();
        $jadwal->guru_mapel_id = $request->integer('guru_mapel_id') ?: null;

        return view('admin.jadwal.form', $this->dataForm($jadwal, $semester));
    }

    public function store(Request $request): RedirectResponse
    {
        $semester = $this->semesterAktif();

        if (! $semester) {
            return $this->tanpaSemester();
        }

        $data = $request->validate(
            ['guru_mapel_id' => ['required', 'exists:guru_mapel,id']] + $this->aturanWaktu(),
            $this->pesan()
        );

        $gm = GuruMapel::with(['guru', 'mapel', 'kelas'])->findOrFail($data['guru_mapel_id']);

        if ((int) $gm->kelas->tahun_ajaran_id !== (int) $semester->tahun_ajaran_id) {
            throw ValidationException::withMessages([
                'guru_mapel_id' => 'Penugasan ini bukan milik tahun ajaran semester aktif.',
            ]);
        }

        $mulai = $this->jam($data['jam_mulai']);
        $selesai = $this->jam($data['jam_selesai']);

        $this->pastikanTidakBentrok($gm, $semester->id, (int) $data['hari_id'], $mulai, $selesai, null);

        JadwalPelajaran::create([
            'guru_mapel_id' => $gm->id,
            'semester_id' => $semester->id,
            'hari_id' => $data['hari_id'],
            'jam_mulai' => $mulai,
            'jam_selesai' => $selesai,
        ]);

        return redirect()->route('admin.jadwal.index', ['kelas_id' => $gm->kelas_id])
            ->with('success', 'Jadwal ditambahkan.');
    }

    public function edit(JadwalPelajaran $jadwal): View|RedirectResponse
    {
        $semester = $this->semesterAktif();

        if (! $semester || (int) $jadwal->semester_id !== (int) $semester->id) {
            return redirect()->route('admin.jadwal.index')
                ->with('error', 'Hanya jadwal semester aktif yang bisa diubah.');
        }

        return view('admin.jadwal.form', $this->dataForm($jadwal, $semester));
    }

    public function update(Request $request, JadwalPelajaran $jadwal): RedirectResponse
    {
        $semester = $this->semesterAktif();

        if (! $semester || (int) $jadwal->semester_id !== (int) $semester->id) {
            return redirect()->route('admin.jadwal.index')
                ->with('error', 'Hanya jadwal semester aktif yang bisa diubah.');
        }

        // Penugasan dikunci, hanya hari dan jam yang boleh berubah.
        $data = $request->validate($this->aturanWaktu(), $this->pesan());

        $gm = $jadwal->guruMapel()->with(['guru', 'mapel', 'kelas'])->first();
        $mulai = $this->jam($data['jam_mulai']);
        $selesai = $this->jam($data['jam_selesai']);

        $this->pastikanTidakBentrok($gm, $semester->id, (int) $data['hari_id'], $mulai, $selesai, $jadwal->id);

        $jadwal->update([
            'hari_id' => $data['hari_id'],
            'jam_mulai' => $mulai,
            'jam_selesai' => $selesai,
        ]);

        return redirect()->route('admin.jadwal.index', ['kelas_id' => $gm->kelas_id])
            ->with('success', 'Jadwal diperbarui.');
    }

    public function destroy(JadwalPelajaran $jadwal): RedirectResponse
    {
        // Jadwal yang sudah dipakai absensi tidak boleh hilang diam-diam.
        if (DB::table('presensi_siswa')->where('jadwal_id', $jadwal->id)->exists()) {
            return back()->with('error', 'Jadwal ini sudah dipakai absensi siswa, tidak bisa dihapus.');
        }

        $jadwal->delete();

        return back()->with('success', 'Jadwal dihapus.');
    }

    private function semesterAktif(): ?Semester
    {
        return Semester::with('tahunAjaran')->where('is_active', true)->first();
    }

    private function tanpaSemester(): RedirectResponse
    {
        return redirect()->route('admin.jadwal.index')
            ->with('error', 'Belum ada semester aktif. Aktifkan semester terlebih dahulu.');
    }

    /** @return array<string, mixed> */
    private function dataForm(JadwalPelajaran $jadwal, Semester $semester): array
    {
        return [
            'jadwal' => $jadwal,
            'semester' => $semester,
            'daftarPenugasan' => GuruMapel::with(['guru', 'mapel', 'kelas'])
                ->whereHas('kelas', fn ($q) => $q->where('tahun_ajaran_id', $semester->tahun_ajaran_id))
                ->get()
                ->sortBy(fn ($g) => $g->kelas->nama_kelas.' '.$g->mapel->nama)
                ->values(),
            'daftarHari' => Hari::where('is_active', true)->orderBy('urutan')->get(),
        ];
    }

    /** @return array<string, mixed> */
    private function aturanWaktu(): array
    {
        return [
            'hari_id' => ['required', Rule::exists('hari', 'id')->where('is_active', true)],
            'jam_mulai' => ['required', 'date_format:H:i'],
            'jam_selesai' => ['required', 'date_format:H:i', 'after:jam_mulai'],
        ];
    }

    /** @return array<string, string> */
    private function pesan(): array
    {
        return [
            'hari_id.exists' => 'Hari yang dipilih bukan hari aktif belajar.',
            'jam_mulai.date_format' => 'Format jam harus JJ:MM.',
            'jam_selesai.date_format' => 'Format jam harus JJ:MM.',
            'jam_selesai.after' => 'Jam selesai harus setelah jam mulai.',
        ];
    }

    /** Jam disimpan dengan detik supaya perbandingan di database seragam. */
    private function jam(string $nilai): string
    {
        return Carbon::createFromFormat('H:i', $nilai)->format('H:i:s');
    }

    /**
     * Dua jadwal beririsan jika mulai lama < selesai baru DAN selesai lama > mulai baru.
     * Jam bersambung (selesai = mulai berikutnya) tidak dihitung bentrok.
     */
    private function irisan(int $semesterId, int $hariId, string $mulai, string $selesai, ?int $abaikanId): Builder
    {
        return JadwalPelajaran::query()
            ->with(['guruMapel.mapel', 'guruMapel.kelas', 'guruMapel.guru'])
            ->where('semester_id', $semesterId)
            ->where('hari_id', $hariId)
            ->where('jam_mulai', '<', $selesai)
            ->where('jam_selesai', '>', $mulai)
            ->when($abaikanId, fn ($q) => $q->where('id', '!=', $abaikanId));
    }

    private function pastikanTidakBentrok(
        GuruMapel $gm,
        int $semesterId,
        int $hariId,
        string $mulai,
        string $selesai,
        ?int $abaikanId,
    ): void {
        $bentrokKelas = $this->irisan($semesterId, $hariId, $mulai, $selesai, $abaikanId)
            ->whereHas('guruMapel', fn ($q) => $q->where('kelas_id', $gm->kelas_id))
            ->first();

        if ($bentrokKelas) {
            throw ValidationException::withMessages([
                'jam_mulai' => 'Bentrok dengan jadwal kelas '.$gm->kelas->nama_kelas.': '.$this->ringkas($bentrokKelas).'.',
            ]);
        }

        $bentrokGuru = $this->irisan($semesterId, $hariId, $mulai, $selesai, $abaikanId)
            ->whereHas('guruMapel', fn ($q) => $q->where('guru_id', $gm->guru_id))
            ->first();

        if ($bentrokGuru) {
            throw ValidationException::withMessages([
                'jam_mulai' => 'Guru '.$gm->guru->nama.' sudah mengajar di jam tersebut: '.$this->ringkas($bentrokGuru).'.',
            ]);
        }
    }

    private function ringkas(JadwalPelajaran $j): string
    {
        return $j->guruMapel->mapel->nama.' di '.$j->guruMapel->kelas->nama_kelas
            .' ('.substr($j->jam_mulai, 0, 5).'-'.substr($j->jam_selesai, 0, 5).')';
    }
}