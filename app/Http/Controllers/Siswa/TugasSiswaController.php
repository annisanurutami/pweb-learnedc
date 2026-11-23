<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\PengumpulanTugas;
use App\Models\Tugas;
use App\Support\KonteksSiswa;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TugasSiswaController extends Controller
{
    private const DISK = 'local';

    /** E3: daftar tugas kelas siswa pada semester aktif. */
    public function index(): View
    {
        $konteks = KonteksSiswa::dari(auth()->user());

        $daftar = collect();
        $pengumpulan = collect();

        if ($konteks->siap()) {
            $daftar = Tugas::with('guruMapel.mapel')
                ->where('semester_id', $konteks->semester->id)
                ->whereHas('guruMapel', fn($q) => $q->where('kelas_id', $konteks->kelas->id))
                ->get()
                // Tugas yang masih terbuka tampil dulu (deadline terdekat), lalu yang sudah lewat (terbaru).
                ->sortBy(fn($t) => $t->deadline->isFuture()
                    ? [0, $t->deadline->timestamp]
                    : [1, -$t->deadline->timestamp])
                ->values();

            $pengumpulan = PengumpulanTugas::where('siswa_id', $konteks->siswa->id)
                ->whereIn('tugas_id', $daftar->pluck('id'))
                ->get()
                ->keyBy('tugas_id');
        }

        return view('siswa.tugas.index', [
            'konteks' => $konteks,
            'daftar' => $daftar,
            'status' => $daftar->mapWithKeys(
                fn($t) => [$t->id => $this->status($t, $pengumpulan->get($t->id))]
            ),
        ]);
    }

    /** Detail tugas beserta form pengumpulan jawaban. */
    public function show(Tugas $tugas): View
    {
        $konteks = KonteksSiswa::dari(auth()->user());
        $this->pastikanMilikKelas($konteks, $tugas);

        $tugas->load('guruMapel.mapel', 'guruMapel.guru');
        $pengumpulan = $this->pengumpulan($konteks, $tugas);

        return view('siswa.tugas.show', [
            'tugas' => $tugas,
            'pengumpulan' => $pengumpulan,
            'status' => $this->status($tugas, $pengumpulan),
            'bisaKumpul' => $this->bisaKumpul($tugas, $pengumpulan),
        ]);
    }

    /** E5: mengumpulkan jawaban, dan boleh mengulang selama sebelum deadline dan belum dinilai. */
    public function kumpul(Request $request, Tugas $tugas): RedirectResponse
    {
        $konteks = KonteksSiswa::dari(auth()->user());
        $this->pastikanMilikKelas($konteks, $tugas);

        $pengumpulan = $this->pengumpulan($konteks, $tugas);

        if ($pengumpulan?->nilai !== null) {
            return redirect()->route('siswa.tugas.show', $tugas)
                ->with('error', 'Tugas sudah dinilai guru, jawaban tidak bisa diubah lagi.');
        }

        if ($tugas->sudahLewatDeadline()) {
            return redirect()->route('siswa.tugas.show', $tugas)
                ->with('error', 'Deadline sudah lewat, tugas ini sudah ditutup.');
        }

        $data = $request->validate([
            'file' => ['nullable', 'file', 'mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,jpg,jpeg,png,zip', 'max:10240'],
            'jawaban_teks' => ['nullable', 'string', 'max:10000'],
        ], [
            'file.mimes' => 'File harus berformat PDF, DOC, DOCX, PPT, PPTX, XLS, XLSX, JPG, PNG, atau ZIP.',
            'file.max' => 'Ukuran file maksimal 10 MB.',
            'file.uploaded' => 'File gagal diunggah. Pastikan ukurannya tidak melebihi 10 MB.',
            'jawaban_teks.max' => 'Jawaban teks maksimal 10.000 karakter.',
        ]);

        $adaFile = $request->hasFile('file') || filled($pengumpulan?->file_path);

        if (!$adaFile && blank($data['jawaban_teks'] ?? null)) {
            throw ValidationException::withMessages([
                'file' => 'Unggah file jawaban atau tulis jawaban teks.',
            ]);
        }

        $path = $pengumpulan?->file_path;

        if ($request->hasFile('file')) {
            $baru = $request->file('file')->store('pengumpulan', self::DISK);

            if ($path) {
                Storage::disk(self::DISK)->delete($path);
            }

            $path = $baru;
        }

        $baris = $pengumpulan ?? new PengumpulanTugas([
            'tugas_id' => $tugas->id,
            'siswa_id' => $konteks->siswa->id,
        ]);

        $baris->file_path = $path;
        $baris->jawaban_teks = $data['jawaban_teks'] ?? null;
        $baris->submitted_at = now();
        $baris->save();

        return redirect()->route('siswa.tugas.show', $tugas)
            ->with('success', $pengumpulan ? 'Jawaban diperbarui.' : 'Jawaban berhasil dikumpulkan.');
    }

    /** Siswa mengunduh lampiran soal dari guru. */
    public function lampiran(Tugas $tugas): StreamedResponse
    {
        $konteks = KonteksSiswa::dari(auth()->user());
        $this->pastikanMilikKelas($konteks, $tugas);

        return $this->unduh($tugas->file_path, Str::slug($tugas->judul) ?: 'tugas');
    }

    /** Siswa mengunduh kembali jawaban miliknya sendiri. */
    public function jawaban(Tugas $tugas): StreamedResponse
    {
        $konteks = KonteksSiswa::dari(auth()->user());
        $this->pastikanMilikKelas($konteks, $tugas);

        $pengumpulan = $this->pengumpulan($konteks, $tugas);

        return $this->unduh($pengumpulan?->file_path, (Str::slug($tugas->judul) ?: 'tugas') . '-jawaban');
    }

    private function unduh(?string $path, string $nama): StreamedResponse
    {
        abort_if(
            !$path || !Storage::disk(self::DISK)->exists($path),
            404,
            'Berkas tidak ditemukan.'
        );

        return Storage::disk(self::DISK)->download($path, $nama . '.' . pathinfo($path, PATHINFO_EXTENSION));
    }

    /** Tugas hanya milik siswa jika ada di kelas dan semester aktif siswa itu. */
    private function pastikanMilikKelas(KonteksSiswa $konteks, Tugas $tugas): void
    {
        abort_unless(
            $konteks->siap()
            && (int) $tugas->semester_id === (int) $konteks->semester->id
            && (int) $tugas->guruMapel->kelas_id === (int) $konteks->kelas->id,
            404
        );
    }

    private function pengumpulan(KonteksSiswa $konteks, Tugas $tugas): ?PengumpulanTugas
    {
        return PengumpulanTugas::where('tugas_id', $tugas->id)
            ->where('siswa_id', $konteks->siswa->id)
            ->first();
    }

    private function bisaKumpul(Tugas $tugas, ?PengumpulanTugas $pengumpulan): bool
    {
        return !$tugas->sudahLewatDeadline() && $pengumpulan?->nilai === null;
    }

    /** @return array{label: string, kelas: string} */
    private function status(Tugas $tugas, ?PengumpulanTugas $pengumpulan): array
    {
        if ($pengumpulan?->nilai !== null) {
            return ['label' => 'Sudah dinilai', 'kelas' => 'bg-green-50 text-green-700'];
        }

        if ($pengumpulan?->submitted_at !== null) {
            return ['label' => 'Sudah dikumpulkan', 'kelas' => 'bg-blue-50 text-blue-700'];
        }

        return $tugas->sudahLewatDeadline()
            ? ['label' => 'Terlewat', 'kelas' => 'bg-red-50 text-red-700']
            : ['label' => 'Belum dikumpulkan', 'kelas' => 'bg-yellow-50 text-yellow-800'];
    }
}