<?php

namespace Tests\Feature;

use App\Models\Guru;
use App\Models\GuruMapel;
use App\Models\Kelas;
use App\Models\KelasSiswa;
use App\Models\MataPelajaran;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminKelasMapelTest extends TestCase
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

    private function tahun(string $nama = '2026/2027'): TahunAjaran
    {
        return TahunAjaran::create([
            'nama' => $nama,
            'tgl_mulai' => '2026-07-13',
            'tgl_selesai' => '2027-06-26',
            'is_active' => true,
        ]);
    }

    private function guru(string $nama): Guru
    {
        $user = User::factory()->create();
        $user->assignRole('guru');

        return Guru::create(['user_id' => $user->id, 'nama' => $nama]);
    }

    private function kelas(TahunAjaran $tahun, string $nama, ?Guru $wali = null): Kelas
    {
        return Kelas::create([
            'tahun_ajaran_id' => $tahun->id,
            'nama_kelas' => $nama,
            'wali_kelas_id' => $wali?->id,
            'iuran_kas' => 0,
        ]);
    }

    public function test_admin_bisa_menambah_mata_pelajaran(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.mata-pelajaran.store'), ['kode' => 'MTK', 'nama' => 'Matematika'])
            ->assertRedirect(route('admin.mata-pelajaran.index'));

        $this->assertDatabaseHas('mata_pelajaran', ['kode' => 'MTK', 'nama' => 'Matematika']);
    }

    public function test_kode_mata_pelajaran_tidak_boleh_kembar(): void
    {
        MataPelajaran::create(['kode' => 'MTK', 'nama' => 'Matematika']);

        $this->actingAs($this->admin())
            ->post(route('admin.mata-pelajaran.store'), ['kode' => 'MTK', 'nama' => 'Lain'])
            ->assertSessionHasErrors('kode');
    }

    public function test_mata_pelajaran_yang_diampu_guru_tidak_bisa_dihapus(): void
    {
        $tahun = $this->tahun();
        $mapel = MataPelajaran::create(['kode' => 'MTK', 'nama' => 'Matematika']);
        GuruMapel::create([
            'guru_id' => $this->guru('Bu Ratna')->id,
            'mapel_id' => $mapel->id,
            'kelas_id' => $this->kelas($tahun, 'X-A')->id,
        ]);

        $this->actingAs($this->admin())
            ->delete(route('admin.mata-pelajaran.destroy', $mapel))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('mata_pelajaran', ['id' => $mapel->id]);
    }

    public function test_membuat_kelas_dengan_wali_memberi_role_wali_kelas(): void
    {
        $tahun = $this->tahun();
        $guru = $this->guru('Bu Ratna');

        $this->actingAs($this->admin())
            ->post(route('admin.kelas.store'), [
                'tahun_ajaran_id' => $tahun->id,
                'nama_kelas' => 'X-A',
                'tingkat' => 'X',
                'wali_kelas_id' => $guru->id,
                'iuran_kas' => 10000,
            ])
            ->assertRedirect(route('admin.kelas.index'));

        $this->assertDatabaseHas('kelas', ['nama_kelas' => 'X-A', 'wali_kelas_id' => $guru->id]);
        $this->assertTrue($guru->user->fresh()->hasRole('wali_kelas'));
    }

    public function test_mengganti_wali_kelas_mencabut_role_dari_guru_lama(): void
    {
        $tahun = $this->tahun();
        $lama = $this->guru('Bu Ratna');
        $baru = $this->guru('Pak Hendra');
        $lama->user->assignRole('wali_kelas');
        $kelas = $this->kelas($tahun, 'X-A', $lama);

        $this->actingAs($this->admin())
            ->put(route('admin.kelas.update', $kelas), [
                'tahun_ajaran_id' => $tahun->id,
                'nama_kelas' => 'X-A',
                'wali_kelas_id' => $baru->id,
                'iuran_kas' => 0,
            ])
            ->assertRedirect(route('admin.kelas.index'));

        $this->assertFalse($lama->user->fresh()->hasRole('wali_kelas'));
        $this->assertTrue($baru->user->fresh()->hasRole('wali_kelas'));
    }

    public function test_satu_guru_tidak_boleh_menjadi_wali_dua_kelas_di_tahun_yang_sama(): void
    {
        $tahun = $this->tahun();
        $guru = $this->guru('Bu Ratna');
        $this->kelas($tahun, 'X-A', $guru);

        $this->actingAs($this->admin())
            ->post(route('admin.kelas.store'), [
                'tahun_ajaran_id' => $tahun->id,
                'nama_kelas' => 'X-B',
                'wali_kelas_id' => $guru->id,
                'iuran_kas' => 0,
            ])
            ->assertSessionHasErrors('wali_kelas_id');
    }

    public function test_kelas_yang_punya_siswa_tidak_bisa_dihapus(): void
    {
        $tahun = $this->tahun();
        $kelas = $this->kelas($tahun, 'X-A');
        $user = User::factory()->create();
        $siswa = Siswa::create([
            'user_id' => $user->id,
            'nis' => '2026001',
            'nama' => 'Siswa Uji',
            'jenis_kelamin' => 'L',
        ]);
        KelasSiswa::create([
            'kelas_id' => $kelas->id,
            'siswa_id' => $siswa->id,
            'tahun_ajaran_id' => $tahun->id,
        ]);

        $this->actingAs($this->admin())
            ->delete(route('admin.kelas.destroy', $kelas))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('kelas', ['id' => $kelas->id]);
    }

    public function test_kelas_kosong_bisa_dihapus_dan_role_wali_dicabut(): void
    {
        $tahun = $this->tahun();
        $guru = $this->guru('Bu Ratna');
        $guru->user->assignRole('wali_kelas');
        $kelas = $this->kelas($tahun, 'X-A', $guru);

        $this->actingAs($this->admin())
            ->delete(route('admin.kelas.destroy', $kelas))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('kelas', ['id' => $kelas->id]);
        $this->assertFalse($guru->user->fresh()->hasRole('wali_kelas'));
    }

    public function test_siswa_tidak_bisa_membuka_daftar_kelas(): void
    {
        $siswa = User::factory()->create();
        $siswa->assignRole('siswa');

        $this->actingAs($siswa)->get(route('admin.kelas.index'))->assertForbidden();
    }
}