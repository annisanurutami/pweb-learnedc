<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use App\Models\GuruMapel;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class GuruMapelController extends Controller
{
    public function index(Request $request): View
    {
        $kelasId = $request->integer('kelas_id') ?: null;

        return view('admin.penugasan.index', [
            'daftar' => GuruMapel::with(['guru', 'mapel', 'kelas.tahunAjaran'])
                ->withCount(['jadwalPelajaran', 'materi', 'tugas', 'ujian'])
                ->when($kelasId, fn ($q) => $q->where('kelas_id', $kelasId))
                ->orderBy('kelas_id')
                ->orderBy('mapel_id')
                ->get(),
            'daftarKelas' => Kelas::with('tahunAjaran')
                ->orderByDesc('tahun_ajaran_id')
                ->orderBy('nama_kelas')
                ->get(),
            'kelasId' => $kelasId,
        ]);
    }

    public function create(Request $request): View
    {
        $penugasan = new GuruMapel();
        $penugasan->kelas_id = $request->integer('kelas_id') ?: null;

        return view('admin.penugasan.form', $this->dataForm($penugasan));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'kelas_id' => ['required', 'exists:kelas,id'],
            'mapel_id' => [
                'required', 'exists:mata_pelajaran,id',
                // Satu mapel di satu kelas hanya punya satu guru pengampu.
                Rule::unique('guru_mapel', 'mapel_id')
                    ->where('kelas_id', $request->input('kelas_id')),
            ],
            'guru_id' => ['required', 'exists:guru,id'],
        ], [
            'mapel_id.unique' => 'Mata pelajaran ini sudah punya guru pengampu di kelas tersebut. Ubah gurunya lewat tombol Ganti guru.',
        ]);

        GuruMapel::create($data);

        return redirect()->route('admin.penugasan.index', ['kelas_id' => $data['kelas_id']])
            ->with('success', 'Penugasan guru ditambahkan.');
    }

    public function edit(GuruMapel $penugasan): View
    {
        return view('admin.penugasan.form', $this->dataForm($penugasan));
    }

    public function update(Request $request, GuruMapel $penugasan): RedirectResponse
    {
        // Hanya guru yang boleh diganti. Kelas dan mapel dikunci karena materi,
        // tugas, dan ujian sudah menempel pada baris penugasan ini.
        $data = $request->validate([
            'guru_id' => ['required', 'exists:guru,id'],
        ]);

        $penugasan->update($data);

        return redirect()->route('admin.penugasan.index', ['kelas_id' => $penugasan->kelas_id])
            ->with('success', 'Guru pengampu diganti.');
    }

    public function destroy(GuruMapel $penugasan): RedirectResponse
    {
        // Relasi ke jadwal, materi, tugas, dan ujian bersifat cascade, jadi tanpa
        // pengecekan ini semuanya ikut terhapus diam-diam.
        if (
            $penugasan->jadwalPelajaran()->exists()
            || $penugasan->materi()->exists()
            || $penugasan->tugas()->exists()
            || $penugasan->ujian()->exists()
        ) {
            return back()->with('error', 'Penugasan ini sudah punya jadwal, materi, tugas, atau ujian, tidak bisa dihapus. Ganti gurunya saja.');
        }

        $penugasan->delete();

        return back()->with('success', 'Penugasan dihapus.');
    }

    /** @return array<string, mixed> */
    private function dataForm(GuruMapel $penugasan): array
    {
        return [
            'penugasan' => $penugasan,
            'daftarKelas' => Kelas::with('tahunAjaran')
                ->orderByDesc('tahun_ajaran_id')
                ->orderBy('nama_kelas')
                ->get(),
            'daftarMapel' => MataPelajaran::orderBy('nama')->get(),
            // Hanya akun aktif, ditambah guru yang sedang menjabat (kalau akunnya sudah nonaktif).
            'daftarGuru' => Guru::whereHas('user', fn ($q) => $q->where('is_active', true))
                ->when($penugasan->guru_id, fn ($q) => $q->orWhere('id', $penugasan->guru_id))
                ->orderBy('nama')
                ->get(),
        ];
    }
}