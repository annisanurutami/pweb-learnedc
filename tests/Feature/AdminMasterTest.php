<?php

namespace Tests\Feature;

use App\Models\Semester;
use App\Models\TahunAjaran;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminMasterTest extends TestCase
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

    private function tahun(string $nama, bool $aktif = false): TahunAjaran
    {
        return TahunAjaran::create([
            'nama' => $nama,
            'tgl_mulai' => '2026-07-13',
            'tgl_selesai' => '2027-06-26',
            'is_active' => $aktif,
        ]);
    }

    private function semester(TahunAjaran $tahun, string $nama, bool $aktif = false): Semester
    {
        return Semester::create([
            'tahun_ajaran_id' => $tahun->id,
            'nama' => $nama,
            'tgl_mulai' => '2026-07-13',
            'tgl_selesai' => '2026-12-19',
            'is_active' => $aktif,
        ]);
    }

    public function test_hanya_admin_yang_bisa_membuka_daftar_tahun_ajaran(): void
    {
        $siswa = User::factory()->create();
        $siswa->assignRole('siswa');

        $this->actingAs($siswa)->get(route('admin.tahun-ajaran.index'))->assertForbidden();
        $this->actingAs($this->admin())->get(route('admin.tahun-ajaran.index'))->assertOk();
    }

    public function test_admin_bisa_menambah_tahun_ajaran(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.tahun-ajaran.store'), [
                'nama' => '2027/2028',
                'tgl_mulai' => '2027-07-12',
                'tgl_selesai' => '2028-06-24',
            ])
            ->assertRedirect(route('admin.tahun-ajaran.index'));

        $this->assertDatabaseHas('tahun_ajaran', ['nama' => '2027/2028', 'is_active' => false]);
    }

    public function test_tanggal_selesai_harus_setelah_tanggal_mulai(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.tahun-ajaran.store'), [
                'nama' => '2027/2028',
                'tgl_mulai' => '2027-07-12',
                'tgl_selesai' => '2027-01-01',
            ])
            ->assertSessionHasErrors('tgl_selesai');
    }

    public function test_mengaktifkan_tahun_ajaran_menonaktifkan_yang_lain(): void
    {
        $lama = $this->tahun('2025/2026', true);
        $baru = $this->tahun('2026/2027');

        $this->actingAs($this->admin())
            ->patch(route('admin.tahun-ajaran.aktifkan', $baru))
            ->assertSessionHas('success');

        $this->assertTrue($baru->fresh()->is_active);
        $this->assertFalse($lama->fresh()->is_active);
    }

    public function test_mengaktifkan_semester_ikut_mengaktifkan_tahun_ajarannya(): void
    {
        $lama = $this->tahun('2025/2026', true);
        $baru = $this->tahun('2026/2027');
        $semesterLama = $this->semester($lama, 'Ganjil', true);
        $semesterBaru = $this->semester($baru, 'Ganjil');

        $this->actingAs($this->admin())
            ->patch(route('admin.semester.aktifkan', $semesterBaru));

        $this->assertTrue($semesterBaru->fresh()->is_active);
        $this->assertTrue($baru->fresh()->is_active);
        $this->assertFalse($semesterLama->fresh()->is_active);
        $this->assertFalse($lama->fresh()->is_active);
    }

    public function test_tahun_ajaran_aktif_tidak_bisa_dihapus(): void
    {
        $aktif = $this->tahun('2026/2027', true);

        $this->actingAs($this->admin())
            ->delete(route('admin.tahun-ajaran.destroy', $aktif))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('tahun_ajaran', ['id' => $aktif->id]);
    }

    public function test_menambah_semester_membuat_bobot_nilai_bawaan(): void
    {
        $tahun = $this->tahun('2026/2027', true);

        $this->actingAs($this->admin())
            ->post(route('admin.semester.store'), [
                'tahun_ajaran_id' => $tahun->id,
                'nama' => 'Ganjil',
                'tgl_mulai' => '2026-07-13',
                'tgl_selesai' => '2026-12-19',
            ])
            ->assertRedirect(route('admin.semester.index'));

        $this->assertDatabaseHas('bobot_nilai', ['bobot_tugas' => 40, 'bobot_ujian' => 60]);
    }

    public function test_semester_yang_sama_tidak_boleh_ganda_dalam_satu_tahun(): void
    {
        $tahun = $this->tahun('2026/2027', true);
        $this->semester($tahun, 'Ganjil');

        $this->actingAs($this->admin())
            ->post(route('admin.semester.store'), [
                'tahun_ajaran_id' => $tahun->id,
                'nama' => 'Ganjil',
                'tgl_mulai' => '2026-07-13',
                'tgl_selesai' => '2026-12-19',
            ])
            ->assertSessionHasErrors('nama');
    }
}