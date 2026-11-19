<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MataPelajaran;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MataPelajaranController extends Controller
{
    public function index(): View
    {
        return view('admin.mata-pelajaran.index', [
            'daftar' => MataPelajaran::withCount('guruMapel')->orderBy('nama')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.mata-pelajaran.form', ['mapel' => new MataPelajaran()]);
    }

    public function store(Request $request): RedirectResponse
    {
        MataPelajaran::create($this->validasi($request));

        return redirect()->route('admin.mata-pelajaran.index')
            ->with('success', 'Mata pelajaran ditambahkan.');
    }

    public function edit(MataPelajaran $mataPelajaran): View
    {
        return view('admin.mata-pelajaran.form', ['mapel' => $mataPelajaran]);
    }

    public function update(Request $request, MataPelajaran $mataPelajaran): RedirectResponse
    {
        $mataPelajaran->update($this->validasi($request, $mataPelajaran));

        return redirect()->route('admin.mata-pelajaran.index')
            ->with('success', 'Mata pelajaran diperbarui.');
    }

    public function destroy(MataPelajaran $mataPelajaran): RedirectResponse
    {
        // Pengecekan manual: relasi guru_mapel bersifat cascade, jadi tanpa ini
        // materi, tugas, dan ujian di mapel tersebut ikut terhapus diam-diam.
        if ($mataPelajaran->guruMapel()->exists()) {
            return back()->with('error', 'Mata pelajaran masih diampu guru di kelas tertentu, tidak bisa dihapus.');
        }

        try {
            $mataPelajaran->delete();
        } catch (QueryException) {
            return back()->with('error', 'Mata pelajaran masih dipakai data lain, tidak bisa dihapus.');
        }

        return back()->with('success', 'Mata pelajaran dihapus.');
    }

    /** @return array<string, mixed> */
    private function validasi(Request $request, ?MataPelajaran $mapel = null): array
    {
        return $request->validate([
            'kode' => [
                'required', 'string', 'max:20',
                Rule::unique('mata_pelajaran', 'kode')->ignore($mapel?->id),
            ],
            'nama' => ['required', 'string', 'max:100'],
        ]);
    }
}