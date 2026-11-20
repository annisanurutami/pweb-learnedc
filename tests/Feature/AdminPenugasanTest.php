<?php

namespace Tests\Feature;

use App\Models\Guru;
use App\Models\GuruMapel;
use App\Models\Kelas;
use App\Models\Materi;
use App\Models\MataPelajaran;
use App\Models\Semester;
use App\Models\TahunAjaran;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPenugasanTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->seed(RoleSeeder::class);
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

    private function tahun(): TahunAjaran
    {
        return TahunAjaran::create([
            'nama' => '2026/2027',
            'tgl_mulai' => '2026-07-13',
            'tgl_selesai' => '2027-06-26',
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

    private function mapel(string $kode = 'MTK'): MataPelajaran
    {
        return MataPelajaran::create(['kode' => $kode, 'nama' => 'Matematika']);
    }

    public function test_guru_tidak_boleh_membuka_penugasan(): void
    {
        $user = User::factory()->create();
        $user->assignRole('guru');

        $this->actingAs($user)->get(route('admin.penugasan.index'))->assertForbidden();
    }

    public function test_admin_bisa_membuka_daftar_penugasan(): void
    {
        $this->actingAs($this->admin())->get(route('admin.penugasan.index'))->assertOk();
    }

    public function test_admin_bisa_menugaskan_guru_ke_mapel_dan_kelas(): void
    {
        $kelas = $this->kelas($this->tahun(), 'X-A');
        $mapel = $this->mapel();
        $guru = $this->guru('Bu Ratna');

        $this->actingAs($this->admin())
            ->post(route('admin.penugasan.store'), [
                'kelas_id' => $kelas->id,
                'mapel_id' => $mapel->id,
                'guru_id' => $guru->id,
            ])
            ->assertRedirect(route('admin.penugasan.index', ['kelas_id' => $kelas->id]));

        $this->assertDatabaseHas('guru_mapel', [
            'kelas_id' => $kelas->id,
            'mapel_id' => $mapel->id,
            'guru_id' => $guru->id,
        ]);
    }

    public function test_mapel_yang_sama_di_kelas_yang_sama_ditolak(): void
    {
        $kelas = $this->kelas($this->tahun(), 'X-A');
        $mapel = $this->mapel();
        GuruMapel::create([
            'kelas_id' => $kelas->id,
            'mapel_id' => $mapel->id,
            'guru_id' => $this->guru('Bu Ratna')->id,
        ]);

        $this->actingAs($this->admin())
            ->post(route('admin.penugasan.store'), [
                'kelas_id' => $kelas->id,
                'mapel_id' => $mapel->id,
                'guru_id' => $this->guru('Pak Budi')->id,
            ])
            ->assertSessionHasErrors('mapel_id');

        $this->assertDatabaseCount('guru_mapel', 1);
    }

    public function test_update_hanya_mengganti_guru(): void
    {
        $tahun = $this->tahun();
        $kelasA = $this->kelas($tahun, 'X-A');
        $kelasB = $this->kelas($tahun, 'X-B');
        $mapel = $this->mapel();
        $lama = $this->guru('Bu Ratna');
        $baru = $this->guru('Pak Budi');

        $penugasan = GuruMapel::create([
            'kelas_id' => $kelasA->id,
            'mapel_id' => $mapel->id,
            'guru_id' => $lama->id,
        ]);

        // kelas_id ikut dikirim, tetapi harus diabaikan.
        $this->actingAs($this->admin())
            ->put(route('admin.penugasan.update', $penugasan), [
                'guru_id' => $baru->id,
                'kelas_id' => $kelasB->id,
            ])
            ->assertRedirect();

        $penugasan->refresh();
        $this->assertSame($baru->id, $penugasan->guru_id);
        $this->assertSame($kelasA->id, $penugasan->kelas_id);
    }

    public function test_penugasan_yang_sudah_punya_materi_tidak_bisa_dihapus(): void
    {
        $tahun = $this->tahun();
        $kelas = $this->kelas($tahun, 'X-A');
        $semester = Semester::create([
            'tahun_ajaran_id' => $tahun->id,
            'nama' => 'Ganjil',
            'tgl_mulai' => '2026-07-13',
            'tgl_selesai' => '2026-12-19',
            'is_active' => true,
        ]);
        $penugasan = GuruMapel::create([
            'kelas_id' => $kelas->id,
            'mapel_id' => $this->mapel()->id,
            'guru_id' => $this->guru('Bu Ratna')->id,
        ]);
        Materi::create([
            'guru_mapel_id' => $penugasan->id,
            'semester_id' => $semester->id,
            'judul' => 'Bab 1',
        ]);

        $this->actingAs($this->admin())
            ->delete(route('admin.penugasan.destroy', $penugasan))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('guru_mapel', ['id' => $penugasan->id]);
    }

    public function test_penugasan_kosong_bisa_dihapus(): void
    {
        $penugasan = GuruMapel::create([
            'kelas_id' => $this->kelas($this->tahun(), 'X-A')->id,
            'mapel_id' => $this->mapel()->id,
            'guru_id' => $this->guru('Bu Ratna')->id,
        ]);

        $this->actingAs($this->admin())
            ->delete(route('admin.penugasan.destroy', $penugasan))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('guru_mapel', ['id' => $penugasan->id]);
    }
}