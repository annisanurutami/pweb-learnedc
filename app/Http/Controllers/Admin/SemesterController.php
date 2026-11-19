<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BobotNilai;
use App\Models\Semester;
use App\Models\TahunAjaran;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SemesterController extends Controller
{
    public function index(): View
    {
        return view('admin.semester.index', [
            'daftar' => Semester::with('tahunAjaran')->orderByDesc('tgl_mulai')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.semester.form', [
            'semester' => new Semester(),
            'daftarTahun' => TahunAjaran::orderByDesc('tgl_mulai')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $semester = Semester::create($this->validasi($request));

        // Setiap semester langsung punya bobot nilai bawaan (tugas 40, ujian 60).
        BobotNilai::firstOrCreate(['semester_id' => $semester->id]);

        return redirect()->route('admin.semester.index')
            ->with('success', 'Semester ditambahkan.');
    }

    public function edit(Semester $semester): View
    {
        return view('admin.semester.form', [
            'semester' => $semester,
            'daftarTahun' => TahunAjaran::orderByDesc('tgl_mulai')->get(),
        ]);
    }

    public function update(Request $request, Semester $semester): RedirectResponse
    {
        $semester->update($this->validasi($request, $semester));

        return redirect()->route('admin.semester.index')
            ->with('success', 'Semester diperbarui.');
    }

    public function destroy(Semester $semester): RedirectResponse
    {
        if ($semester->is_active) {
            return back()->with('error', 'Semester yang sedang aktif tidak bisa dihapus.');
        }

        try {
            $semester->delete();
        } catch (QueryException) {
            return back()->with('error', 'Semester masih dipakai (jadwal, tugas, nilai, dan lainnya), tidak bisa dihapus.');
        }

        return back()->with('success', 'Semester dihapus.');
    }

    /** Hanya satu semester aktif, dan tahun ajarannya otomatis ikut aktif. */
    public function aktifkan(Semester $semester): RedirectResponse
    {
        DB::transaction(function () use ($semester) {
            Semester::where('is_active', true)->update(['is_active' => false]);
            TahunAjaran::where('is_active', true)->update(['is_active' => false]);

            $semester->update(['is_active' => true]);
            TahunAjaran::whereKey($semester->tahun_ajaran_id)->update(['is_active' => true]);
        });

        return back()->with('success', "Semester {$semester->nama} diaktifkan.");
    }

    /** @return array<string, mixed> */
    private function validasi(Request $request, ?Semester $semester = null): array
    {
        return $request->validate([
            'tahun_ajaran_id' => ['required', 'exists:tahun_ajaran,id'],
            'nama' => [
                'required', Rule::in(['Ganjil', 'Genap']),
                Rule::unique('semester', 'nama')
                    ->where('tahun_ajaran_id', $request->input('tahun_ajaran_id'))
                    ->ignore($semester?->id),
            ],
            'tgl_mulai' => ['required', 'date'],
            'tgl_selesai' => ['required', 'date', 'after:tgl_mulai'],
        ]);
    }
}