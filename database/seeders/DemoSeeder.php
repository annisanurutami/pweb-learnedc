<?php

namespace Database\Seeders;

use App\Models\BobotNilai;
use App\Models\Guru;
use App\Models\Hari;
use App\Models\JadwalPelajaran;
use App\Models\JadwalPiket;
use App\Models\Kelas;
use App\Models\KelasSiswa;
use App\Models\GuruMapel;
use App\Models\MataPelajaran;
use App\Models\RoleSiswa;
use App\Models\Semester;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        // ---------- Tahun ajaran, semester, bobot ----------
        $tahun = TahunAjaran::updateOrCreate(
            ['nama' => '2026/2027'],
            ['tgl_mulai' => '2026-07-13', 'tgl_selesai' => '2027-06-26', 'is_active' => true],
        );

        $ganjil = Semester::updateOrCreate(
            ['tahun_ajaran_id' => $tahun->id, 'nama' => 'Ganjil'],
            ['tgl_mulai' => '2026-07-13', 'tgl_selesai' => '2026-12-19', 'is_active' => true],
        );
        Semester::updateOrCreate(
            ['tahun_ajaran_id' => $tahun->id, 'nama' => 'Genap'],
            ['tgl_mulai' => '2027-01-04', 'tgl_selesai' => '2027-06-26', 'is_active' => false],
        );

        BobotNilai::updateOrCreate(
            ['semester_id' => $ganjil->id],
            ['bobot_tugas' => 40, 'bobot_ujian' => 60],
        );

        // ---------- Akun non-guru, non-siswa ----------
        $this->akun('Super Admin', 'admin@sekolah.test', ['super_admin']);
        $this->akun('Kepala Sekolah Demo', 'kepsek@sekolah.test', ['kepala_sekolah']);

        // ---------- Guru ----------
        $waliA = $this->guru('Bu Ratna', 'walikelas@sekolah.test', '198001012005012001', ['guru', 'wali_kelas']);
        $waliB = $this->guru('Pak Hendra', 'walikelas2@sekolah.test', '198202022006041002', ['guru', 'wali_kelas']);
        $guru = $this->guru('Pak Agus', 'guru@sekolah.test', '198503032010011003', ['guru']);

        // ---------- Kelas ----------
        $kelasA = Kelas::updateOrCreate(
            ['tahun_ajaran_id' => $tahun->id, 'nama_kelas' => 'X-A'],
            ['tingkat' => 'X', 'wali_kelas_id' => $waliA->id, 'iuran_kas' => 10000],
        );
        $kelasB = Kelas::updateOrCreate(
            ['tahun_ajaran_id' => $tahun->id, 'nama_kelas' => 'X-B'],
            ['tingkat' => 'X', 'wali_kelas_id' => $waliB->id, 'iuran_kas' => 10000],
        );

        // ---------- Mata pelajaran ----------
        $mtk = MataPelajaran::updateOrCreate(['kode' => 'MTK'], ['nama' => 'Matematika']);
        $bin = MataPelajaran::updateOrCreate(['kode' => 'BIN'], ['nama' => 'Bahasa Indonesia']);
        $ipa = MataPelajaran::updateOrCreate(['kode' => 'IPA'], ['nama' => 'Ilmu Pengetahuan Alam']);

        // ---------- Penugasan guru (guru_mapel) ----------
        $gmMtkA = $this->guruMapel($waliA, $mtk, $kelasA);
        $gmMtkB = $this->guruMapel($waliA, $mtk, $kelasB);
        $gmBinA = $this->guruMapel($guru, $bin, $kelasA);
        $gmBinB = $this->guruMapel($guru, $bin, $kelasB);
        $gmIpaA = $this->guruMapel($guru, $ipa, $kelasA);

        // ---------- Jadwal pelajaran ----------
        $this->jadwal($gmMtkA, $ganjil, 'Senin', '07:30', '09:00');
        $this->jadwal($gmBinA, $ganjil, 'Senin', '09:15', '10:45');
        $this->jadwal($gmIpaA, $ganjil, 'Selasa', '07:30', '09:00');
        $this->jadwal($gmMtkB, $ganjil, 'Rabu', '07:30', '09:00');
        $this->jadwal($gmBinB, $ganjil, 'Rabu', '09:15', '10:45');

        // ---------- Siswa ----------
        // [nama, email, NIS, kelas, role tambahan, jabatan di kelas, jenis kelamin]
        $daftarSiswa = [
            ['Siswa Demo', 'siswa@sekolah.test', '2026001', $kelasA, [], null, 'L'],
            ['Bendahara Demo', 'bendahara@sekolah.test', '2026002', $kelasA, ['bendahara'], 'bendahara', 'P'],
            ['Citra Lestari', 'siswa3@sekolah.test', '2026003', $kelasA, [], 'ketua', 'P'],
            ['Dewi Anggraini', 'siswa4@sekolah.test', '2026004', $kelasA, [], null, 'P'],
            ['Eka Putra', 'siswa5@sekolah.test', '2026005', $kelasA, [], null, 'L'],
            ['Fitri Handayani', 'siswa6@sekolah.test', '2026006', $kelasB, [], null, 'P'],
            ['Gilang Ramadhan', 'siswa7@sekolah.test', '2026007', $kelasB, [], null, 'L'],
            ['Hana Safitri', 'siswa8@sekolah.test', '2026008', $kelasB, [], null, 'P'],
        ];

        $dibuat = [];
        foreach ($daftarSiswa as [$nama, $email, $nis, $kelas, $roleTambahan, $jabatan, $jk]) {
            $user = $this->akun($nama, $email, array_merge(['siswa'], $roleTambahan));

            $siswa = Siswa::updateOrCreate(
                ['user_id' => $user->id],
                ['nis' => $nis, 'nama' => $nama, 'jenis_kelamin' => $jk, 'tgl_lahir' => '2010-01-01'],
            );

            KelasSiswa::updateOrCreate(
                ['siswa_id' => $siswa->id, 'tahun_ajaran_id' => $tahun->id],
                ['kelas_id' => $kelas->id],
            );

            if ($jabatan !== null) {
                RoleSiswa::updateOrCreate(
                    ['kelas_id' => $kelas->id, 'semester_id' => $ganjil->id, 'role' => $jabatan],
                    ['siswa_id' => $siswa->id],
                );
            }

            $dibuat[$email] = $siswa;
        }

        // ---------- Jadwal piket kelas X-A ----------
        $this->piket($kelasA, $ganjil, $dibuat['siswa@sekolah.test'], 'Senin');
        $this->piket($kelasA, $ganjil, $dibuat['bendahara@sekolah.test'], 'Selasa');
    }

    /** @param array<int, string> $roles */
    private function akun(string $nama, string $email, array $roles): User
    {
        $user = User::firstOrNew(['email' => $email]);
        $user->name = $nama;
        $user->password = 'password'; // di-hash otomatis oleh cast 'hashed'
        $user->email_verified_at = now();
        $user->is_active = true;
        $user->save();

        $user->syncRoles($roles);

        return $user;
    }

    /** @param array<int, string> $roles */
    private function guru(string $nama, string $email, string $nip, array $roles): Guru
    {
        $user = $this->akun($nama, $email, $roles);

        return Guru::updateOrCreate(
            ['user_id' => $user->id],
            ['nip' => $nip, 'nama' => $nama],
        );
    }

    private function guruMapel(Guru $guru, MataPelajaran $mapel, Kelas $kelas): GuruMapel
    {
        return GuruMapel::updateOrCreate(
            ['kelas_id' => $kelas->id, 'mapel_id' => $mapel->id],
            ['guru_id' => $guru->id],
        );
    }

    private function jadwal(GuruMapel $gm, Semester $semester, string $namaHari, string $mulai, string $selesai): void
    {
        JadwalPelajaran::updateOrCreate(
            [
                'guru_mapel_id' => $gm->id,
                'semester_id' => $semester->id,
                'hari_id' => Hari::where('nama_hari', $namaHari)->value('id'),
                'jam_mulai' => $mulai,
            ],
            ['jam_selesai' => $selesai],
        );
    }

    private function piket(Kelas $kelas, Semester $semester, Siswa $siswa, string $namaHari): void
    {
        JadwalPiket::updateOrCreate(
            [
                'semester_id' => $semester->id,
                'siswa_id' => $siswa->id,
                'hari_id' => Hari::where('nama_hari', $namaHari)->value('id'),
            ],
            ['kelas_id' => $kelas->id],
        );
    }
}