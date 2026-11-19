<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\KelasSiswa;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SiswaController extends Controller
{
    private const PESAN_TANPA_TAHUN = 'Belum ada tahun ajaran aktif. Aktifkan dulu di menu Tahun Ajaran.';

    public function index(Request $request): View
    {
        $tahun = TahunAjaran::where('is_active', true)->first();
        $tahunId = $tahun?->id;

        $query = Siswa::with([
            'user',
            'kelasSiswa' => fn ($q) => $q->where('tahun_ajaran_id', $tahunId)->with('kelas'),
        ])->orderBy('nama');

        if ($request->filled('kelas')) {
            $kelasId = $request->integer('kelas');
            $query->whereHas('kelasSiswa', fn ($q) => $q
                ->where('kelas_id', $kelasId)
                ->where('tahun_ajaran_id', $tahunId));
        }

        return view('admin.siswa.index', [
            'daftar' => $query->paginate(15)->withQueryString(),
            'daftarKelas' => $this->daftarKelas($tahunId),
            'tahunAktif' => $tahun,
        ]);
    }

    public function create(): View
    {
        $tahun = TahunAjaran::where('is_active', true)->first();

        return view('admin.siswa.form', [
            'siswa' => new Siswa(),
            'daftarKelas' => $this->daftarKelas($tahun?->id),
            'tahunAktif' => $tahun,
            'kelasSekarang' => null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $tahun = TahunAjaran::where('is_active', true)->first();

        if (! $tahun) {
            return back()->withInput()->with('error', self::PESAN_TANPA_TAHUN);
        }

        $data = $this->validasi($request, $tahun->id);

        DB::transaction(function () use ($data, $tahun) {
            $user = new User();
            $user->name = $data['nama'];
            $user->email = $data['email'];
            $user->password = $data['password'];
            $user->email_verified_at = now();
            $user->is_active = true;
            $user->save();
            $user->assignRole('siswa');

            $siswa = Siswa::create([
                'user_id' => $user->id,
                'nis' => $data['nis'],
                'nisn' => $data['nisn'] ?? null,
                'nama' => $data['nama'],
                'jenis_kelamin' => $data['jenis_kelamin'],
                'tgl_lahir' => $data['tgl_lahir'] ?? null,
            ]);

            $this->tempatkanDiKelas($siswa, $data['kelas_id'] ?? null, $tahun->id);
        });

        return redirect()->route('admin.siswa.index')->with('success', 'Akun siswa ditambahkan.');
    }

    public function edit(Siswa $siswa): View
    {
        $tahun = TahunAjaran::where('is_active', true)->first();

        return view('admin.siswa.form', [
            'siswa' => $siswa->load('user'),
            'daftarKelas' => $this->daftarKelas($tahun?->id),
            'tahunAktif' => $tahun,
            'kelasSekarang' => $tahun
                ? $siswa->kelasSiswa()->where('tahun_ajaran_id', $tahun->id)->value('kelas_id')
                : null,
        ]);
    }

    public function update(Request $request, Siswa $siswa): RedirectResponse
    {
        $tahun = TahunAjaran::where('is_active', true)->first();

        if (! $tahun) {
            return back()->withInput()->with('error', self::PESAN_TANPA_TAHUN);
        }

        $data = $this->validasi($request, $tahun->id, $siswa);

        DB::transaction(function () use ($data, $siswa, $tahun) {
            $siswa->update([
                'nis' => $data['nis'],
                'nisn' => $data['nisn'] ?? null,
                'nama' => $data['nama'],
                'jenis_kelamin' => $data['jenis_kelamin'],
                'tgl_lahir' => $data['tgl_lahir'] ?? null,
            ]);

            $user = $siswa->user;
            $user->name = $data['nama'];
            $user->email = $data['email'];
            if (! empty($data['password'])) {
                $user->password = $data['password'];
            }
            $user->save();

            $this->tempatkanDiKelas($siswa, $data['kelas_id'] ?? null, $tahun->id);
        });

        return redirect()->route('admin.siswa.index')->with('success', 'Data siswa diperbarui.');
    }

    /** Akun tidak dihapus karena nilai, absensi, dan kas menunjuk padanya. Cukup dinonaktifkan. */
    public function ubahStatus(Siswa $siswa): RedirectResponse
    {
        $user = $siswa->user;
        $user->is_active = ! $user->is_active;
        $user->save();

        return back()->with('success', $user->is_active ? 'Akun siswa diaktifkan.' : 'Akun siswa dinonaktifkan.');
    }

    /** Satu siswa hanya berada di satu kelas per tahun ajaran, jadi baris yang sama diperbarui. */
    private function tempatkanDiKelas(Siswa $siswa, int|string|null $kelasId, int $tahunId): void
    {
        if ($kelasId === null || $kelasId === '') {
            KelasSiswa::where('siswa_id', $siswa->id)->where('tahun_ajaran_id', $tahunId)->delete();

            return;
        }

        KelasSiswa::updateOrCreate(
            ['siswa_id' => $siswa->id, 'tahun_ajaran_id' => $tahunId],
            ['kelas_id' => (int) $kelasId],
        );
    }

    private function daftarKelas(?int $tahunId)
    {
        return $tahunId
            ? Kelas::where('tahun_ajaran_id', $tahunId)->orderBy('nama_kelas')->get()
            : collect();
    }

    /** @return array<string, mixed> */
    private function validasi(Request $request, int $tahunId, ?Siswa $siswa = null): array
    {
        return $request->validate([
            'nama' => ['required', 'string', 'max:100'],
            'email' => [
                'required', 'email', 'max:255',
                Rule::unique('users', 'email')->ignore($siswa?->user_id),
            ],
            'nis' => ['required', 'string', 'max:20', Rule::unique('siswa', 'nis')->ignore($siswa?->id)],
            'nisn' => ['nullable', 'string', 'max:20', Rule::unique('siswa', 'nisn')->ignore($siswa?->id)],
            'jenis_kelamin' => ['required', Rule::in(['L', 'P'])],
            'tgl_lahir' => ['nullable', 'date', 'before:today'],
            // Kelas harus milik tahun ajaran yang sedang aktif.
            'kelas_id' => ['nullable', Rule::exists('kelas', 'id')->where('tahun_ajaran_id', $tahunId)],
            'password' => [$siswa ? 'nullable' : 'required', 'string', 'min:8'],
        ]);
    }
}