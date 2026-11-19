<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Semester;
use App\Models\TahunAjaran;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TahunAjaranController extends Controller
{
    public function index(): View
    {
        return view('admin.tahun-ajaran.index', [
            'daftar' => TahunAjaran::withCount(['semester', 'kelas'])->orderByDesc('tgl_mulai')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.tahun-ajaran.form', ['tahunAjaran' => new TahunAjaran()]);
    }

    public function store(Request $request): RedirectResponse
    {
        TahunAjaran::create($this->validasi($request));

        return redirect()->route('admin.tahun-ajaran.index')
            ->with('success', 'Tahun ajaran ditambahkan.');
    }

    public function edit(TahunAjaran $tahunAjaran): View
    {
        return view('admin.tahun-ajaran.form', ['tahunAjaran' => $tahunAjaran]);
    }

    public function update(Request $request, TahunAjaran $tahunAjaran): RedirectResponse
    {
        $tahunAjaran->update($this->validasi($request, $tahunAjaran));

        return redirect()->route('admin.tahun-ajaran.index')
            ->with('success', 'Tahun ajaran diperbarui.');
    }

    public function destroy(TahunAjaran $tahunAjaran): RedirectResponse
    {
        if ($tahunAjaran->is_active) {
            return back()->with('error', 'Tahun ajaran yang sedang aktif tidak bisa dihapus.');
        }

        try {
            $tahunAjaran->delete();
        } catch (QueryException) {
            return back()->with('error', 'Tahun ajaran masih dipakai (semester atau kelas), tidak bisa dihapus.');
        }

        return back()->with('success', 'Tahun ajaran dihapus.');
    }

    /** Hanya satu tahun ajaran yang boleh aktif. Semester aktif ikut dimatikan agar tidak tertinggal di tahun lain. */
    public function aktifkan(TahunAjaran $tahunAjaran): RedirectResponse
    {
        DB::transaction(function () use ($tahunAjaran) {
            TahunAjaran::where('is_active', true)->update(['is_active' => false]);
            Semester::where('is_active', true)->update(['is_active' => false]);
            $tahunAjaran->update(['is_active' => true]);
        });

        return back()->with('success', "Tahun ajaran {$tahunAjaran->nama} diaktifkan. Aktifkan juga semesternya.");
    }

    /** @return array<string, mixed> */
    private function validasi(Request $request, ?TahunAjaran $tahunAjaran = null): array
    {
        return $request->validate([
            'nama' => [
                'required',
                'string',
                'max:20',
                Rule::unique('tahun_ajaran', 'nama')->ignore($tahunAjaran?->id),
            ],
            'tgl_mulai' => ['required', 'date'],
            'tgl_selesai' => ['required', 'date', 'after:tgl_mulai'],
        ]);
    }
}