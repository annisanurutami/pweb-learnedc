<?php

namespace App\Support;

use App\Models\Kelas;
use App\Models\KelasSiswa;
use App\Models\Semester;
use App\Models\Siswa;
use App\Models\User;

/**
 * Konteks akademik siswa yang sedang login: data siswa, semester aktif,
 * dan kelas pada tahun ajaran semester itu. Semua halaman siswa bekerja dari sini,
 * sehingga siswa hanya pernah melihat data kelasnya sendiri.
 */
final class KonteksSiswa
{
    public function __construct(
        public readonly Siswa $siswa,
        public readonly ?Semester $semester,
        public readonly ?Kelas $kelas,
    ) {
    }

    public static function dari(User $user): self
    {
        $siswa = $user->siswa;

        abort_if($siswa === null, 403, 'Akun ini belum terhubung ke data siswa. Hubungi administrator.');

        $semester = Semester::with('tahunAjaran')->where('is_active', true)->first();
        $kelas = null;

        if ($semester) {
            $kelas = Kelas::where('tahun_ajaran_id', $semester->tahun_ajaran_id)
                ->whereIn('id', KelasSiswa::where('siswa_id', $siswa->id)->select('kelas_id'))
                ->first();
        }

        return new self($siswa, $semester, $kelas);
    }

    /** Ada semester aktif dan siswa terdaftar di sebuah kelas pada tahun ajarannya. */
    public function siap(): bool
    {
        return $this->semester !== null && $this->kelas !== null;
    }
}