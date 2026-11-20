<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\GuruMapel;
use App\Models\KelasSiswa;
use App\Models\PengumpulanTugas;
use App\Models\Semester;
use App\Models\Siswa;
use App\Models\Tugas;
use App\Services\PenilaianService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class NilaiTugasController extends Controller
{
    private const DISK = 'local';

    /** C3.1 dan C3.2: daftar siswa kelas beserta status pengumpulan dan nilainya. */
    public function index(GuruMapel $penugasan, Tugas $tugas): View|RedirectResponse
    {
        $this->periksa($penugasan, $tugas);

        if ($tolak = $this->tolakJikaBukanSemesterAktif($penugasan, $tugas)) {
            return $tolak;
        }

        $penugasan->load(['kelas', 'mapel']);

        return view('guru.nilai.index', [
            'penugasan' => $penugasan,
            'tugas' => $tugas,
            'daftarSiswa' => $this->siswaKelas($penugasan),
            'pengumpulan' => $tugas->pengumpulan()->get()->keyBy('siswa_id'),
        ]);
    }

    /** Menyimpan nilai banyak siswa sekaligus. Kolom nilai yang kosong dilewati. */
    public function update(
        Request $request,
        GuruMapel $penugasan,
        Tugas $tugas,
        PenilaianService $penilaian,
    ): RedirectResponse {
        $this->periksa($penugasan, $tugas);

        if ($tolak = $this->tolakJikaBukanSemesterAktif($penugasan, $tugas)) {
            return $tolak;
        }

        $data = $request->validate([
            'nilai' => ['required', 'array'],
            'nilai.*' => ['nullable', 'numeric', 'between:0,100', 'decimal:0,2'],
            'catatan' => ['nullable', 'array'],
            'catatan.*' => ['nullable', 'string', 'max:1000'],
        ], $this->pesan());

        $siswaKelas = $this->siswaKelas($penugasan)->keyBy('id');
        $idDikirim = array_map('intval', array_keys($data['nilai']));

        if (array_diff($idDikirim, $siswaKelas->keys()->map(fn ($id) => (int) $id)->all()) !== []) {
            throw ValidationException::withMessages([
                'nilai' => 'Ada siswa yang tidak terdaftar di kelas ini.',
            ]);
        }

        $tersimpan = 0;

        DB::transaction(function () use ($data, $siswaKelas, $tugas, $penilaian, $request, &$tersimpan) {
            foreach ($data['nilai'] as $siswaId => $nilai) {
                if ($nilai === null || $nilai === '') {
                    continue;
                }

                $penilaian->simpanNilaiTugas(
                    $tugas,
                    $siswaKelas[(int) $siswaId],
                    (float) $nilai,
                    $data['catatan'][$siswaId] ?? null,
                    $request->user(),
                );

                $tersimpan++;
            }
        });

        return redirect()
            ->route('guru.nilai.index', ['penugasan' => $penugasan, 'tugas' => $tugas])
            ->with('success', $tersimpan > 0 ? $tersimpan.' nilai disimpan.' : 'Tidak ada nilai yang diisi.');
    }

    /** Guru mengunduh berkas jawaban seorang siswa untuk dinilai. */
    public function berkas(GuruMapel $penugasan, Tugas $tugas, Siswa $siswa): StreamedResponse
    {
        $this->periksa($penugasan, $tugas);

        abort_unless(
            KelasSiswa::where('kelas_id', $penugasan->kelas_id)->where('siswa_id', $siswa->id)->exists(),
            404
        );

        $pengumpulan = PengumpulanTugas::where('tugas_id', $tugas->id)
            ->where('siswa_id', $siswa->id)
            ->first();

        abort_if(
            ! $pengumpulan
                || ! $pengumpulan->file_path
                || ! Storage::disk(self::DISK)->exists($pengumpulan->file_path),
            404,
            'Berkas pengumpulan tidak ditemukan.'
        );

        $nama = Str::slug($siswa->nama.' '.$tugas->judul) ?: 'pengumpulan';
        $ekstensi = pathinfo($pengumpulan->file_path, PATHINFO_EXTENSION);

        return Storage::disk(self::DISK)->download($pengumpulan->file_path, $nama.'.'.$ekstensi);
    }

    /** Guru harus pemilik penugasan, dan tugas harus milik penugasan itu. */
    private function periksa(GuruMapel $penugasan, Tugas $tugas): void
    {
        Gate::authorize('kelola', $penugasan);

        abort_if((int) $tugas->guru_mapel_id !== (int) $penugasan->id, 404);
    }

    private function tolakJikaBukanSemesterAktif(GuruMapel $penugasan, Tugas $tugas): ?RedirectResponse
    {
        $aktif = Semester::where('is_active', true)->value('id');

        if ($aktif !== null && (int) $aktif === (int) $tugas->semester_id) {
            return null;
        }

        return redirect()->route('guru.tugas.index', $penugasan)
            ->with('error', 'Hanya tugas pada semester aktif yang bisa dinilai.');
    }

    /** @return Collection<int, Siswa> */
    private function siswaKelas(GuruMapel $penugasan): Collection
    {
        return Siswa::whereIn(
            'id',
            KelasSiswa::where('kelas_id', $penugasan->kelas_id)->select('siswa_id')
        )->orderBy('nama')->get();
    }

    /** @return array<string, string> */
    private function pesan(): array
    {
        return [
            'nilai.required' => 'Tidak ada data nilai yang dikirim.',
            'nilai.*.numeric' => 'Nilai harus berupa angka.',
            'nilai.*.between' => 'Nilai harus antara 0 dan 100.',
            'nilai.*.decimal' => 'Nilai maksimal dua angka di belakang koma.',
            'catatan.*.max' => 'Catatan maksimal 1000 karakter.',
        ];
    }
}