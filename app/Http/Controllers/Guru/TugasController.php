<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\GuruMapel;
use App\Models\Semester;
use App\Models\Tugas;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TugasController extends Controller
{
    /** Disk privat: lampiran hanya bisa diambil lewat route yang dijaga policy. */
    private const DISK = 'local';

    /** Format nilai dari <input type="datetime-local">. */
    private const FORMAT_DEADLINE = 'Y-m-d\TH:i';

    public function index(GuruMapel $penugasan): View
    {
        Gate::authorize('kelola', $penugasan);

        $semester = $this->semesterAktif();
        $penugasan->load(['kelas', 'mapel']);

        $daftar = $semester
            ? $penugasan->tugas()
                ->where('semester_id', $semester->id)
                ->withCount(['pengumpulan' => fn ($q) => $q->whereNotNull('submitted_at')])
                ->orderByDesc('deadline')
                ->orderByDesc('id')
                ->get()
            : collect();

        return view('guru.tugas.index', [
            'penugasan' => $penugasan,
            'semester' => $semester,
            'daftar' => $daftar,
            'jumlahSiswa' => $penugasan->kelas->kelasSiswa()->count(),
        ]);
    }

    public function create(GuruMapel $penugasan): View|RedirectResponse
    {
        Gate::authorize('kelola', $penugasan);

        if (! $this->semesterAktif()) {
            return $this->tanpaSemester($penugasan);
        }

        $penugasan->load(['kelas', 'mapel']);

        return view('guru.tugas.form', [
            'penugasan' => $penugasan,
            'tugas' => new Tugas(),
        ]);
    }

    public function store(Request $request, GuruMapel $penugasan): RedirectResponse
    {
        Gate::authorize('kelola', $penugasan);

        $semester = $this->semesterAktif();

        if (! $semester) {
            return $this->tanpaSemester($penugasan);
        }

        $data = $request->validate($this->aturan(), $this->pesan());
        $deadline = $this->waktu($data['deadline']);

        if ($deadline->isPast()) {
            throw ValidationException::withMessages([
                'deadline' => 'Deadline harus di masa depan.',
            ]);
        }

        $path = $request->hasFile('lampiran')
            ? $request->file('lampiran')->store('tugas', self::DISK)
            : null;

        Tugas::create([
            'guru_mapel_id' => $penugasan->id,
            'semester_id' => $semester->id,
            'judul' => $data['judul'],
            'deskripsi' => $data['deskripsi'] ?? null,
            'file_path' => $path,
            'deadline' => $deadline,
        ]);

        return redirect()->route('guru.tugas.index', $penugasan)
            ->with('success', 'Tugas ditambahkan.');
    }

    public function edit(GuruMapel $penugasan, Tugas $tugas): View
    {
        $this->periksa($penugasan, $tugas);

        $penugasan->load(['kelas', 'mapel']);

        return view('guru.tugas.form', [
            'penugasan' => $penugasan,
            'tugas' => $tugas,
        ]);
    }

    public function update(Request $request, GuruMapel $penugasan, Tugas $tugas): RedirectResponse
    {
        $this->periksa($penugasan, $tugas);

        $data = $request->validate(
            $this->aturan() + ['hapus_lampiran' => ['nullable', 'boolean']],
            $this->pesan()
        );

        $path = $tugas->file_path;

        if ($request->hasFile('lampiran')) {
            $baru = $request->file('lampiran')->store('tugas', self::DISK);

            if ($path) {
                Storage::disk(self::DISK)->delete($path);
            }

            $path = $baru;
        } elseif ($request->boolean('hapus_lampiran') && $path) {
            Storage::disk(self::DISK)->delete($path);
            $path = null;
        }

        // Saat mengubah, deadline boleh di masa lalu (misalnya untuk menutup lebih awal).
        $tugas->update([
            'judul' => $data['judul'],
            'deskripsi' => $data['deskripsi'] ?? null,
            'file_path' => $path,
            'deadline' => $this->waktu($data['deadline']),
        ]);

        return redirect()->route('guru.tugas.index', $penugasan)
            ->with('success', 'Tugas diperbarui.');
    }

    public function destroy(GuruMapel $penugasan, Tugas $tugas): RedirectResponse
    {
        $this->periksa($penugasan, $tugas);

        // Pengumpulan dan nilai siswa ikut terhapus oleh cascade, jadi diblokir.
        if ($tugas->pengumpulan()->exists()) {
            return back()->with('error', 'Tugas ini sudah dikumpulkan siswa, tidak bisa dihapus.');
        }

        $path = $tugas->file_path;
        $tugas->delete();

        if ($path) {
            Storage::disk(self::DISK)->delete($path);
        }

        return redirect()->route('guru.tugas.index', $penugasan)
            ->with('success', 'Tugas dihapus.');
    }

    public function lampiran(GuruMapel $penugasan, Tugas $tugas): StreamedResponse
    {
        $this->periksa($penugasan, $tugas);

        abort_if(
            ! $tugas->file_path || ! Storage::disk(self::DISK)->exists($tugas->file_path),
            404,
            'Lampiran tidak ditemukan.'
        );

        $nama = Str::slug($tugas->judul) ?: 'tugas';
        $ekstensi = pathinfo($tugas->file_path, PATHINFO_EXTENSION);

        return Storage::disk(self::DISK)->download($tugas->file_path, $nama.'.'.$ekstensi);
    }

    /** Guru harus pemilik penugasan, dan tugas harus milik penugasan itu. */
    private function periksa(GuruMapel $penugasan, Tugas $tugas): void
    {
        Gate::authorize('kelola', $penugasan);

        abort_if((int) $tugas->guru_mapel_id !== (int) $penugasan->id, 404);
    }

    private function semesterAktif(): ?Semester
    {
        return Semester::where('is_active', true)->first();
    }

    private function tanpaSemester(GuruMapel $penugasan): RedirectResponse
    {
        return redirect()->route('guru.tugas.index', $penugasan)
            ->with('error', 'Belum ada semester aktif. Hubungi administrator.');
    }

    /** '!' mereset detik ke 0, kalau tidak detiknya ikut detik saat ini. */
    private function waktu(string $nilai): Carbon
    {
        return Carbon::createFromFormat('!'.self::FORMAT_DEADLINE, $nilai, config('app.timezone'));
    }

    /** @return array<string, mixed> */
    private function aturan(): array
    {
        return [
            'judul' => ['required', 'string', 'max:200'],
            'deskripsi' => ['nullable', 'string', 'max:5000'],
            'deadline' => ['required', 'date_format:'.self::FORMAT_DEADLINE],
            'lampiran' => ['nullable', 'file', 'mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,jpg,jpeg,png', 'max:10240'],
        ];
    }

    /** @return array<string, string> */
    private function pesan(): array
    {
        return [
            'judul.required' => 'Judul tugas wajib diisi.',
            'deadline.required' => 'Deadline wajib diisi.',
            'deadline.date_format' => 'Format deadline tidak valid.',
            'lampiran.mimes' => 'Lampiran harus berformat PDF, DOC, DOCX, PPT, PPTX, XLS, XLSX, JPG, atau PNG.',
            'lampiran.max' => 'Ukuran lampiran maksimal 10 MB.',
            'lampiran.uploaded' => 'Lampiran gagal diunggah. Pastikan ukurannya tidak melebihi 10 MB.',
        ];
    }
}