<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\GuruMapel;
use App\Models\Materi;
use App\Models\Semester;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MateriController extends Controller
{
    /** Disk privat: file hanya bisa diambil lewat route yang dijaga policy. */
    private const DISK = 'local';

    public function index(GuruMapel $penugasan): View
    {
        Gate::authorize('kelola', $penugasan);

        $semester = $this->semesterAktif();
        $penugasan->load(['kelas', 'mapel']);

        $daftar = $semester
            ? $penugasan->materi()
                ->where('semester_id', $semester->id)
                ->orderByDesc('created_at')
                ->orderByDesc('id')
                ->get()
            : collect();

        return view('guru.materi.index', [
            'penugasan' => $penugasan,
            'semester' => $semester,
            'daftar' => $daftar,
        ]);
    }

    public function create(GuruMapel $penugasan): View|RedirectResponse
    {
        Gate::authorize('kelola', $penugasan);

        if (! $this->semesterAktif()) {
            return $this->tanpaSemester($penugasan);
        }

        $penugasan->load(['kelas', 'mapel']);

        return view('guru.materi.form', [
            'penugasan' => $penugasan,
            'materi' => new Materi(),
        ]);
    }

    public function store(Request $request, GuruMapel $penugasan): RedirectResponse
    {
        Gate::authorize('kelola', $penugasan);

        $semester = $this->semesterAktif();

        if (! $semester) {
            return $this->tanpaSemester($penugasan);
        }

        $data = $request->validate([
            'judul' => ['required', 'string', 'max:200'],
            'deskripsi' => ['nullable', 'string', 'max:2000'],
            'file' => ['nullable', 'file', 'mimes:pdf,doc,docx,ppt,pptx', 'max:10240', 'required_without:link_url'],
            'link_url' => ['nullable', 'url:http,https', 'max:500'],
        ], $this->pesan());

        $path = $request->hasFile('file')
            ? $request->file('file')->store('materi', self::DISK)
            : null;

        Materi::create([
            'guru_mapel_id' => $penugasan->id,
            'semester_id' => $semester->id,
            'judul' => $data['judul'],
            'deskripsi' => $data['deskripsi'] ?? null,
            'file_path' => $path,
            'link_url' => $data['link_url'] ?? null,
        ]);

        return redirect()->route('guru.materi.index', $penugasan)
            ->with('success', 'Materi ditambahkan.');
    }

    public function edit(GuruMapel $penugasan, Materi $materi): View
    {
        $this->periksa($penugasan, $materi);

        $penugasan->load(['kelas', 'mapel']);

        return view('guru.materi.form', [
            'penugasan' => $penugasan,
            'materi' => $materi,
        ]);
    }

    public function update(Request $request, GuruMapel $penugasan, Materi $materi): RedirectResponse
    {
        $this->periksa($penugasan, $materi);

        $data = $request->validate([
            'judul' => ['required', 'string', 'max:200'],
            'deskripsi' => ['nullable', 'string', 'max:2000'],
            'file' => ['nullable', 'file', 'mimes:pdf,doc,docx,ppt,pptx', 'max:10240'],
            'link_url' => ['nullable', 'url:http,https', 'max:500'],
        ], $this->pesan());

        // Materi harus tetap punya file atau link setelah diubah.
        if (! $request->hasFile('file') && ! $materi->file_path && blank($data['link_url'] ?? null)) {
            throw ValidationException::withMessages([
                'file' => 'Materi harus punya file atau link video.',
            ]);
        }

        $path = $materi->file_path;

        if ($request->hasFile('file')) {
            $baru = $request->file('file')->store('materi', self::DISK);

            if ($materi->file_path) {
                Storage::disk(self::DISK)->delete($materi->file_path);
            }

            $path = $baru;
        }

        $materi->update([
            'judul' => $data['judul'],
            'deskripsi' => $data['deskripsi'] ?? null,
            'file_path' => $path,
            'link_url' => $data['link_url'] ?? null,
        ]);

        return redirect()->route('guru.materi.index', $penugasan)
            ->with('success', 'Materi diperbarui.');
    }

    public function destroy(GuruMapel $penugasan, Materi $materi): RedirectResponse
    {
        $this->periksa($penugasan, $materi);

        $path = $materi->file_path;
        $materi->delete();

        if ($path) {
            Storage::disk(self::DISK)->delete($path);
        }

        return redirect()->route('guru.materi.index', $penugasan)
            ->with('success', 'Materi dihapus.');
    }

    public function unduh(GuruMapel $penugasan, Materi $materi): StreamedResponse
    {
        $this->periksa($penugasan, $materi);

        abort_if(
            ! $materi->file_path || ! Storage::disk(self::DISK)->exists($materi->file_path),
            404,
            'File materi tidak ditemukan.'
        );

        $nama = Str::slug($materi->judul) ?: 'materi';
        $ekstensi = pathinfo($materi->file_path, PATHINFO_EXTENSION);

        return Storage::disk(self::DISK)->download($materi->file_path, $nama.'.'.$ekstensi);
    }

    /** Guru harus pemilik penugasan, dan materi harus milik penugasan itu. */
    private function periksa(GuruMapel $penugasan, Materi $materi): void
    {
        Gate::authorize('kelola', $penugasan);

        abort_if((int) $materi->guru_mapel_id !== (int) $penugasan->id, 404);
    }

    private function semesterAktif(): ?Semester
    {
        return Semester::where('is_active', true)->first();
    }

    private function tanpaSemester(GuruMapel $penugasan): RedirectResponse
    {
        return redirect()->route('guru.materi.index', $penugasan)
            ->with('error', 'Belum ada semester aktif. Hubungi administrator.');
    }

    /** @return array<string, string> */
    private function pesan(): array
    {
        return [
            'judul.required' => 'Judul materi wajib diisi.',
            'file.mimes' => 'File harus berformat PDF, DOC, DOCX, PPT, atau PPTX.',
            'file.max' => 'Ukuran file maksimal 10 MB.',
            'file.uploaded' => 'File gagal diunggah. Pastikan ukurannya tidak melebihi 10 MB.',
            'file.required_without' => 'Unggah file materi atau isi link video.',
            'link_url.url' => 'Link harus diawali http:// atau https://.',
        ];
    }
}