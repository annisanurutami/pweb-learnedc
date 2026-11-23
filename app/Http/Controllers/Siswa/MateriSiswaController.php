<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\Materi;
use App\Support\KonteksSiswa;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MateriSiswaController extends Controller
{
    private const DISK = 'local';

    /** Materi dari guru untuk kelas siswa pada semester aktif, dikelompokkan per mata pelajaran. */
    public function index(): View
    {
        $konteks = KonteksSiswa::dari(auth()->user());

        $perMapel = collect();

        if ($konteks->siap()) {
            $perMapel = Materi::with('guruMapel.mapel')
                ->where('semester_id', $konteks->semester->id)
                ->whereHas('guruMapel', fn($q) => $q->where('kelas_id', $konteks->kelas->id))
                ->orderByDesc('created_at')
                ->orderByDesc('id')
                ->get()
                ->groupBy(fn($m) => $m->guruMapel->mapel->nama)
                ->sortKeys();
        }

        return view('siswa.materi.index', [
            'konteks' => $konteks,
            'perMapel' => $perMapel,
        ]);
    }

    public function unduh(Materi $materi): StreamedResponse
    {
        $konteks = KonteksSiswa::dari(auth()->user());

        abort_unless(
            $konteks->siap()
            && (int) $materi->semester_id === (int) $konteks->semester->id
            && (int) $materi->guruMapel->kelas_id === (int) $konteks->kelas->id,
            404
        );

        abort_if(
            !$materi->file_path || !Storage::disk(self::DISK)->exists($materi->file_path),
            404,
            'File materi tidak ditemukan.'
        );

        $nama = Str::slug($materi->judul) ?: 'materi';

        return Storage::disk(self::DISK)->download(
            $materi->file_path,
            $nama . '.' . pathinfo($materi->file_path, PATHINFO_EXTENSION)
        );
    }
}