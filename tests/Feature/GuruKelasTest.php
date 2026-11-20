<?php

namespace Tests\Feature;

use App\Models\Guru;
use App\Models\GuruMapel;
use App\Models\Hari;
use App\Models\JadwalPelajaran;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\Semester;
use App\Models\TahunAjaran;
use App\Models\User;
use Database\Seeders\HariSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GuruKelasTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->seed(RoleSeeder::class);
        $this->seed(HariSeeder::class);
    }

    /** @param array<int, string> $roles */
    private function akunGuru(string $nama, array $roles = ['guru']): User
    {
        $user = User::factory()->create();
        $user->assignRole($roles);
        Guru::create(['user_id' => $user->id, 'nama' => $nama]);

        return $user;
    }

    private function tahun(string $nama = '2026/2027', bool $aktif = true): TahunAjaran
    {
        return TahunAjaran::create([
            'nama' => $nama,
            'tgl_mulai' => '2026-07-13',
            'tgl_selesai' => '2027-06-26',
            'is_active' => $aktif,
        ]);
    }

    private function semesterAktif(TahunAjaran $tahun): Semester
    {
        return Semester::create([
            'tahun_ajaran_id' => $tahun->id,
            'nama' => 'Ganjil',
            'tgl_mulai' => '2026-07-13',
            'tgl_selesai' => '2026-12-19',
            'is_active' => true,
        ]);
    }

    private function penugasan(TahunAjaran $tahun, User $akunGuru, string $namaKelas, string $kodeMapel, string $namaMapel): GuruMapel
    {
        $kelas = Kelas::create([
            'tahun_ajaran_id' => $tahun->id,
            'nama_kelas' => $namaKelas,
            'iuran_kas' => 0,
        ]);
        $mapel = MataPelajaran::create(['kode' => $kodeMapel, 'nama' => $namaMapel]);

        return GuruMapel::create([
            'kelas_id' => $kelas->id,
            'mapel_id' => $mapel->id,
            'guru_id' => Guru::where('user_id', $akunGuru->id)->value('id'),
        ]);
    }

    public function test_siswa_tidak_boleh_membuka_kelas_saya(): void
    {
        $siswa = User::factory()->create();
        $siswa->assignRole('siswa');

        $this->actingAs($siswa)->get(route('guru.kelas.index'))->assertForbidden();
    }

    public function test_guru_hanya_melihat_kelas_miliknya(): void
    {
        $tahun = $this->tahun();
        $this->semesterAktif($tahun);
        $ratna = $this->akunGuru('Bu Ratna');
        $agus = $this->akunGuru('Pak Agus');
        $this->penugasan($tahun, $ratna, 'X-A', 'MTK', 'Matematika');
        $this->penugasan($tahun, $agus, 'X-B', 'BIN', 'Bahasa Indonesia');

        $this->actingAs($ratna)
            ->get(route('guru.kelas.index'))
            ->assertOk()
            ->assertSee('Matematika')
            ->assertSee('X-A')
            ->assertDontSee('Bahasa Indonesia')
            ->assertDontSee('X-B');
    }

    public function test_tanpa_semester_aktif_ada_peringatan(): void
    {
        $guru = $this->akunGuru('Bu Ratna');

        $this->actingAs($guru)
            ->get(route('guru.kelas.index'))
            ->assertOk()
            ->assertSee('Belum ada semester aktif');
    }

    public function test_penugasan_tahun_ajaran_lain_tidak_muncul(): void
    {
        $this->semesterAktif($this->tahun('2026/2027'));
        $lama = $this->tahun('2025/2026', false);
        $guru = $this->akunGuru('Bu Ratna');
        $this->penugasan($lama, $guru, 'IX-Z', 'MTK', 'Matematika');

        $this->actingAs($guru)
            ->get(route('guru.kelas.index'))
            ->assertOk()
            ->assertDontSee('IX-Z')
            ->assertSee('belum ditugaskan');
    }

    public function test_jadwal_tampil_di_daftar_dan_halaman_kelas(): void
    {
        $tahun = $this->tahun();
        $semester = $this->semesterAktif($tahun);
        $guru = $this->akunGuru('Bu Ratna');
        $penugasan = $this->penugasan($tahun, $guru, 'X-A', 'MTK', 'Matematika');

        JadwalPelajaran::create([
            'guru_mapel_id' => $penugasan->id,
            'semester_id' => $semester->id,
            'hari_id' => Hari::where('nama_hari', 'Senin')->value('id'),
            'jam_mulai' => '07:30:00',
            'jam_selesai' => '09:00:00',
        ]);

        $this->actingAs($guru)
            ->get(route('guru.kelas.index'))
            ->assertSee('Senin, 07:30 - 09:00');

        $this->actingAs($guru)
            ->get(route('guru.kelas.show', $penugasan))
            ->assertOk()
            ->assertSee('Senin, 07:30 - 09:00');
    }

    public function test_guru_bisa_membuka_kelas_yang_diampunya(): void
    {
        $tahun = $this->tahun();
        $this->semesterAktif($tahun);
        $guru = $this->akunGuru('Bu Ratna');
        $penugasan = $this->penugasan($tahun, $guru, 'X-A', 'MTK', 'Matematika');

        $this->actingAs($guru)
            ->get(route('guru.kelas.show', $penugasan))
            ->assertOk()
            ->assertSee('Matematika')
            ->assertSee('X-A');
    }

    public function test_guru_tidak_bisa_membuka_kelas_milik_guru_lain(): void
    {
        $tahun = $this->tahun();
        $this->semesterAktif($tahun);
        $ratna = $this->akunGuru('Bu Ratna');
        $agus = $this->akunGuru('Pak Agus');
        $punyaAgus = $this->penugasan($tahun, $agus, 'X-B', 'BIN', 'Bahasa Indonesia');

        $this->actingAs($ratna)
            ->get(route('guru.kelas.show', $punyaAgus))
            ->assertForbidden();
    }

    public function test_wali_kelas_juga_bisa_membuka_kelas_saya(): void
    {
        $tahun = $this->tahun();
        $this->semesterAktif($tahun);
        $wali = $this->akunGuru('Bu Ratna', ['guru', 'wali_kelas']);
        $penugasan = $this->penugasan($tahun, $wali, 'X-A', 'MTK', 'Matematika');

        $this->actingAs($wali)->get(route('guru.kelas.index'))->assertOk()->assertSee('Matematika');
        $this->actingAs($wali)->get(route('guru.kelas.show', $penugasan))->assertOk();
    }

    public function test_akun_guru_tanpa_data_guru_ditolak(): void
    {
        $user = User::factory()->create();
        $user->assignRole('guru'); // tidak ada baris di tabel guru

        $this->actingAs($user)->get(route('guru.kelas.index'))->assertForbidden();
    }
}