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

class AdminJadwalTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->seed(RoleSeeder::class);
        $this->seed(HariSeeder::class); // Senin sampai Jumat aktif, Sabtu dan Minggu nonaktif
    }

    private function admin(): User
    {
        $user = User::factory()->create();
        $user->assignRole('super_admin');

        return $user;
    }

    private function guru(string $nama): Guru
    {
        $user = User::factory()->create();
        $user->assignRole('guru');

        return Guru::create(['user_id' => $user->id, 'nama' => $nama]);
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

    private function kelas(TahunAjaran $tahun, string $nama): Kelas
    {
        return Kelas::create([
            'tahun_ajaran_id' => $tahun->id,
            'nama_kelas' => $nama,
            'iuran_kas' => 0,
        ]);
    }

    private function penugasan(Kelas $kelas, Guru $guru, string $kode): GuruMapel
    {
        $mapel = MataPelajaran::firstOrCreate(['kode' => $kode], ['nama' => 'Mapel '.$kode]);

        return GuruMapel::create([
            'kelas_id' => $kelas->id,
            'mapel_id' => $mapel->id,
            'guru_id' => $guru->id,
        ]);
    }

    private function hari(string $nama): int
    {
        return Hari::where('nama_hari', $nama)->value('id');
    }

    private function jadwal(GuruMapel $gm, Semester $semester, string $hari, string $mulai, string $selesai): JadwalPelajaran
    {
        return JadwalPelajaran::create([
            'guru_mapel_id' => $gm->id,
            'semester_id' => $semester->id,
            'hari_id' => $this->hari($hari),
            'jam_mulai' => $mulai,
            'jam_selesai' => $selesai,
        ]);
    }

    /** @return array<string, mixed> */
    private function data(GuruMapel $gm, string $hari, string $mulai, string $selesai): array
    {
        return [
            'guru_mapel_id' => $gm->id,
            'hari_id' => $this->hari($hari),
            'jam_mulai' => $mulai,
            'jam_selesai' => $selesai,
        ];
    }

    public function test_guru_tidak_boleh_membuka_jadwal(): void
    {
        $user = User::factory()->create();
        $user->assignRole('guru');

        $this->actingAs($user)->get(route('admin.jadwal.index'))->assertForbidden();
    }

    public function test_admin_bisa_menambah_jadwal(): void
    {
        $tahun = $this->tahun();
        $semester = $this->semesterAktif($tahun);
        $kelas = $this->kelas($tahun, 'X-A');
        $gm = $this->penugasan($kelas, $this->guru('Bu Ratna'), 'MTK');

        $this->actingAs($this->admin())
            ->post(route('admin.jadwal.store'), $this->data($gm, 'Senin', '07:30', '09:00'))
            ->assertRedirect(route('admin.jadwal.index', ['kelas_id' => $kelas->id]));

        $this->assertDatabaseHas('jadwal_pelajaran', [
            'guru_mapel_id' => $gm->id,
            'semester_id' => $semester->id,
            'hari_id' => $this->hari('Senin'),
            'jam_mulai' => '07:30:00',
            'jam_selesai' => '09:00:00',
        ]);
    }

    public function test_tanpa_semester_aktif_jadwal_ditolak(): void
    {
        $tahun = $this->tahun();
        $kelas = $this->kelas($tahun, 'X-A');
        $gm = $this->penugasan($kelas, $this->guru('Bu Ratna'), 'MTK');

        $this->actingAs($this->admin())
            ->post(route('admin.jadwal.store'), $this->data($gm, 'Senin', '07:30', '09:00'))
            ->assertSessionHas('error');

        $this->assertDatabaseCount('jadwal_pelajaran', 0);
    }

    public function test_jadwal_bentrok_dengan_kelas_yang_sama_ditolak(): void
    {
        $tahun = $this->tahun();
        $semester = $this->semesterAktif($tahun);
        $kelas = $this->kelas($tahun, 'X-A');
        $gm1 = $this->penugasan($kelas, $this->guru('Bu Ratna'), 'MTK');
        $gm2 = $this->penugasan($kelas, $this->guru('Pak Budi'), 'BIN');
        $this->jadwal($gm1, $semester, 'Senin', '07:30:00', '09:00:00');

        $this->actingAs($this->admin())
            ->post(route('admin.jadwal.store'), $this->data($gm2, 'Senin', '08:00', '09:30'))
            ->assertSessionHasErrors('jam_mulai');

        $this->assertDatabaseCount('jadwal_pelajaran', 1);
    }

    public function test_guru_yang_sama_tidak_bisa_mengajar_dua_kelas_sekaligus(): void
    {
        $tahun = $this->tahun();
        $semester = $this->semesterAktif($tahun);
        $guru = $this->guru('Bu Ratna');
        $gm1 = $this->penugasan($this->kelas($tahun, 'X-A'), $guru, 'MTK');
        $gm2 = $this->penugasan($this->kelas($tahun, 'X-B'), $guru, 'BIN');
        $this->jadwal($gm1, $semester, 'Senin', '07:30:00', '09:00:00');

        $this->actingAs($this->admin())
            ->post(route('admin.jadwal.store'), $this->data($gm2, 'Senin', '08:00', '09:00'))
            ->assertSessionHasErrors('jam_mulai');

        $this->assertDatabaseCount('jadwal_pelajaran', 1);
    }

    public function test_jam_bersambung_tidak_dianggap_bentrok(): void
    {
        $tahun = $this->tahun();
        $semester = $this->semesterAktif($tahun);
        $kelas = $this->kelas($tahun, 'X-A');
        $gm1 = $this->penugasan($kelas, $this->guru('Bu Ratna'), 'MTK');
        $gm2 = $this->penugasan($kelas, $this->guru('Pak Budi'), 'BIN');
        $this->jadwal($gm1, $semester, 'Senin', '07:30:00', '09:00:00');

        $this->actingAs($this->admin())
            ->post(route('admin.jadwal.store'), $this->data($gm2, 'Senin', '09:00', '10:30'))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseCount('jadwal_pelajaran', 2);
    }

    public function test_jam_selesai_harus_setelah_jam_mulai(): void
    {
        $tahun = $this->tahun();
        $this->semesterAktif($tahun);
        $gm = $this->penugasan($this->kelas($tahun, 'X-A'), $this->guru('Bu Ratna'), 'MTK');

        $this->actingAs($this->admin())
            ->post(route('admin.jadwal.store'), $this->data($gm, 'Senin', '08:00', '07:00'))
            ->assertSessionHasErrors('jam_selesai');
    }

    public function test_hari_nonaktif_ditolak(): void
    {
        $tahun = $this->tahun();
        $this->semesterAktif($tahun);
        $gm = $this->penugasan($this->kelas($tahun, 'X-A'), $this->guru('Bu Ratna'), 'MTK');

        $this->actingAs($this->admin())
            ->post(route('admin.jadwal.store'), $this->data($gm, 'Sabtu', '07:30', '09:00'))
            ->assertSessionHasErrors('hari_id');
    }

    public function test_penugasan_dari_tahun_ajaran_lain_ditolak(): void
    {
        $this->semesterAktif($this->tahun('2026/2027'));
        $tahunLama = $this->tahun('2025/2026', false);
        $gm = $this->penugasan($this->kelas($tahunLama, 'X-A'), $this->guru('Bu Ratna'), 'MTK');

        $this->actingAs($this->admin())
            ->post(route('admin.jadwal.store'), $this->data($gm, 'Senin', '07:30', '09:00'))
            ->assertSessionHasErrors('guru_mapel_id');

        $this->assertDatabaseCount('jadwal_pelajaran', 0);
    }

    public function test_mengubah_jadwal_tidak_bentrok_dengan_dirinya_sendiri(): void
    {
        $tahun = $this->tahun();
        $semester = $this->semesterAktif($tahun);
        $gm = $this->penugasan($this->kelas($tahun, 'X-A'), $this->guru('Bu Ratna'), 'MTK');
        $jadwal = $this->jadwal($gm, $semester, 'Senin', '07:30:00', '09:00:00');

        $this->actingAs($this->admin())
            ->put(route('admin.jadwal.update', $jadwal), [
                'hari_id' => $this->hari('Senin'),
                'jam_mulai' => '07:45',
                'jam_selesai' => '09:15',
            ])
            ->assertSessionHasNoErrors();

        $jadwal->refresh();
        $this->assertSame('07:45', substr($jadwal->jam_mulai, 0, 5));
        $this->assertSame('09:15', substr($jadwal->jam_selesai, 0, 5));
    }

    public function test_admin_bisa_menghapus_jadwal(): void
    {
        $tahun = $this->tahun();
        $semester = $this->semesterAktif($tahun);
        $gm = $this->penugasan($this->kelas($tahun, 'X-A'), $this->guru('Bu Ratna'), 'MTK');
        $jadwal = $this->jadwal($gm, $semester, 'Senin', '07:30:00', '09:00:00');

        $this->actingAs($this->admin())
            ->delete(route('admin.jadwal.destroy', $jadwal))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('jadwal_pelajaran', ['id' => $jadwal->id]);
    }
}