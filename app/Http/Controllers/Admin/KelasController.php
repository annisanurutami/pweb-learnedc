<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use App\Models\Kelas;
use App\Models\TahunAjaran;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class KelasController extends Controller
{
    public function index(): View
    {
        return view('admin.kelas.index', [
            'daftar' => Kelas::with(['tahunAjaran', 'waliKelas'])
                ->withCount(['kelasSiswa', 'guruMapel'])
                ->orderByDesc('tahun_ajaran_id')
                ->orderBy('nama_kelas')
                ->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.kelas.form', $this->dataForm(new Kelas()));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validasi($request);

        DB::transaction(function () use ($data) {
            $kelas = Kelas::create($data);
            $this->sinkronkanRoleWali([$kelas->wali_kelas_id]);
        });

        return redirect()->route('admin.kelas.index')->with('success', 'Kelas ditambahkan.');
    }

    public function edit(Kelas $kelas): View
    {
        return view('admin.kelas.form', $this->dataForm($kelas));
    }

    public function update(Request $request, Kelas $kelas): RedirectResponse
    {
        $data = $this->validasi($request, $kelas);
        $waliLama = $kelas->wali_kelas_id;

        DB::transaction(function () use ($kelas, $data, $waliLama) {
            $kelas->update($data);
            $this->sinkronkanRoleWali([$waliLama, $kelas->wali_kelas_id]);
        });

        return redirect()->route('admin.kelas.index')->with('success', 'Kelas diperbarui.');
    }

    public function destroy(Kelas $kelas): RedirectResponse
    {
        // Kelas yang sudah punya siswa atau guru pengampu tidak boleh dihapus
        // karena relasinya cascade dan akan menghapus data turunannya.
        if ($kelas->kelasSiswa()->exists() || $kelas->guruMapel()->exists()) {
            return back()->with('error', 'Kelas masih punya siswa atau guru pengampu, tidak bisa dihapus.');
        }

        try {
            DB::transaction(function () use ($kelas) {
                $waliId = $kelas->wali_kelas_id;
                $kelas->delete();
                $this->sinkronkanRoleWali([$waliId]);
            });
        } catch (QueryException) {
            return back()->with('error', 'Kelas masih dipakai data lain, tidak bisa dihapus.');
        }

        return back()->with('success', 'Kelas dihapus.');
    }

    /** @return array<string, mixed> */
    private function dataForm(Kelas $kelas): array
    {
        return [
            'kelas' => $kelas,
            'daftarTahun' => TahunAjaran::orderByDesc('tgl_mulai')->get(),
            'daftarGuru' => Guru::orderBy('nama')->get(),
            'tahunAktifId' => TahunAjaran::where('is_active', true)->value('id'),
        ];
    }

    /** @return array<string, mixed> */
    private function validasi(Request $request, ?Kelas $kelas = null): array
    {
        return $request->validate([
            'tahun_ajaran_id' => ['required', 'exists:tahun_ajaran,id'],
            'nama_kelas' => [
                'required', 'string', 'max:30',
                Rule::unique('kelas', 'nama_kelas')
                    ->where('tahun_ajaran_id', $request->input('tahun_ajaran_id'))
                    ->ignore($kelas?->id),
            ],
            'tingkat' => ['nullable', 'string', 'max:10'],
            'wali_kelas_id' => [
                'nullable', 'exists:guru,id',
                // Satu guru hanya menjadi wali satu kelas dalam satu tahun ajaran.
                Rule::unique('kelas', 'wali_kelas_id')
                    ->where('tahun_ajaran_id', $request->input('tahun_ajaran_id'))
                    ->ignore($kelas?->id),
            ],
            'iuran_kas' => ['required', 'numeric', 'min:0', 'max:99999999'],
        ], [
            'wali_kelas_id.unique' => 'Guru ini sudah menjadi wali kelas lain di tahun ajaran tersebut.',
            'nama_kelas.unique' => 'Nama kelas sudah dipakai di tahun ajaran tersebut.',
        ]);
    }

    /**
     * Role wali_kelas mengikuti kolom kelas.wali_kelas_id:
     * diberikan ke guru yang menjadi wali, dicabut dari guru yang tidak lagi menjadi wali.
     *
     * @param  array<int, int|null>  $guruIds
     */
    private function sinkronkanRoleWali(array $guruIds): void
    {
        foreach (array_filter(array_unique($guruIds)) as $guruId) {
            $user = Guru::find($guruId)?->user;

            if (! $user) {
                continue;
            }

            if (Kelas::where('wali_kelas_id', $guruId)->exists()) {
                $user->assignRole('wali_kelas');
            } else {
                $user->removeRole('wali_kelas');
            }
        }
    }
}