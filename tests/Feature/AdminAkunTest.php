<?php

namespace Tests\Feature;

use App\Models\Guru;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminAkunTest extends TestCase
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

    private function tahun(string $nama = '2026/2027', bool $aktif = true): TahunAjaran
    {
        return TahunAjaran::create([
            'nama' => $nama,
            'tgl_mulai' => '2026-07-13',
            'tgl_selesai' => '2027-06-26',
            'is_active' => $aktif,
        ]);
    }

    private function kelas(TahunAjaran $tahun, string $nama): Kelas
    {
        return Kelas::create(['tahun_ajaran_id' => $tahun->id, 'nama_kelas' => $nama, 'iuran_kas' => 0]);
    }

    /** @return array<string, mixed> */
    private function dataSiswa(array $ubah = []): array
    {
        return array_merge([
            'nama' => 'Dewi Anggraini',
            'email' => 'dewi@sekolah.test',
            'nis' => '2026004',
            'jenis_kelamin' => 'P',
            'tgl_lahir' => '2010-05-01',
            'password' => 'rahasia123',
        ], $ubah);
    }

    public function test_admin_bisa_menambah_guru_beserta_akunnya(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.guru.store'), [
                'nama' => 'Bu Sari',
                'email' => 'sari@sekolah.test',
                'nip' => '198001012005012009',
                'no_hp' => '0812345678',
                'password' => 'rahasia123',
            ])
            ->assertRedirect(route('admin.guru.index'));

        $user = User::where('email', 'sari@sekolah.test')->firstOrFail();
        $this->assertTrue($user->hasRole('guru'));
        $this->assertTrue($user->is_active);
        $this->assertTrue(Hash::check('rahasia123', $user->password));
        $this->assertDatabaseHas('guru', ['user_id' => $user->id, 'nama' => 'Bu Sari']);
    }

    public function test_email_guru_tidak_boleh_kembar(): void
    {
        User::factory()->create(['email' => 'sari@sekolah.test']);

        $this->actingAs($this->admin())
            ->post(route('admin.guru.store'), [
                'nama' => 'Bu Sari',
                'email' => 'sari@sekolah.test',
                'password' => 'rahasia123',
            ])
            ->assertSessionHasErrors('email');
    }

    public function test_password_wajib_saat_membuat_tapi_opsional_saat_mengubah(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->post(route('admin.guru.store'), ['nama' => 'Bu Sari', 'email' => 'sari@sekolah.test'])
            ->assertSessionHasErrors('password');

        $user = User::factory()->create(['email' => 'sari@sekolah.test']);
        $guru = Guru::create(['user_id' => $user->id, 'nama' => 'Bu Sari']);
        $passwordLama = $user->password;

        $this->actingAs($admin)
            ->put(route('admin.guru.update', $guru), ['nama' => 'Bu Sari Dewi', 'email' => 'sari@sekolah.test'])
            ->assertRedirect(route('admin.guru.index'));

        $this->assertSame($passwordLama, $user->fresh()->password);
        $this->assertSame('Bu Sari Dewi', $user->fresh()->name);
        $this->assertSame('Bu Sari Dewi', $guru->fresh()->nama);
    }

    public function test_akun_guru_bisa_dinonaktifkan_dan_diaktifkan_kembali(): void
    {
        $user = User::factory()->create();
        $guru = Guru::create(['user_id' => $user->id, 'nama' => 'Bu Sari']);
        $admin = $this->admin();

        $this->actingAs($admin)->patch(route('admin.guru.status', $guru));
        $this->assertFalse($user->fresh()->is_active);

        $this->actingAs($admin)->patch(route('admin.guru.status', $guru));
        $this->assertTrue($user->fresh()->is_active);
    }

    public function test_admin_bisa_menambah_siswa_dan_menempatkannya_di_kelas_aktif(): void
    {
        $tahun = $this->tahun();
        $kelas = $this->kelas($tahun, 'X-A');

        $this->actingAs($this->admin())
            ->post(route('admin.siswa.store'), $this->dataSiswa(['kelas_id' => $kelas->id]))
            ->assertRedirect(route('admin.siswa.index'));

        $user = User::where('email', 'dewi@sekolah.test')->firstOrFail();
        $this->assertTrue($user->hasRole('siswa'));

        $siswa = Siswa::where('user_id', $user->id)->firstOrFail();
        $this->assertDatabaseHas('kelas_siswa', [
            'siswa_id' => $siswa->id,
            'kelas_id' => $kelas->id,
            'tahun_ajaran_id' => $tahun->id,
        ]);
    }

    public function test_siswa_tidak_bisa_ditempatkan_di_kelas_tahun_ajaran_lain(): void
    {
        $this->tahun('2026/2027', true);
        $lain = $this->tahun('2025/2026', false);
        $kelasLain = $this->kelas($lain, 'X-A');

        $this->actingAs($this->admin())
            ->post(route('admin.siswa.store'), $this->dataSiswa(['kelas_id' => $kelasLain->id]))
            ->assertSessionHasErrors('kelas_id');

        $this->assertDatabaseMissing('users', ['email' => 'dewi@sekolah.test']);
    }

    public function test_menambah_siswa_tanpa_tahun_ajaran_aktif_ditolak(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.siswa.store'), $this->dataSiswa())
            ->assertSessionHas('error');

        $this->assertDatabaseMissing('users', ['email' => 'dewi@sekolah.test']);
    }

    public function test_memindahkan_siswa_memperbarui_baris_kelas_yang_sama(): void
    {
        $tahun = $this->tahun();
        $kelasA = $this->kelas($tahun, 'X-A');
        $kelasB = $this->kelas($tahun, 'X-B');
        $admin = $this->admin();

        $this->actingAs($admin)
            ->post(route('admin.siswa.store'), $this->dataSiswa(['kelas_id' => $kelasA->id]));

        $siswa = Siswa::firstOrFail();

        $this->actingAs($admin)
            ->put(route('admin.siswa.update', $siswa), $this->dataSiswa(['kelas_id' => $kelasB->id]))
            ->assertRedirect(route('admin.siswa.index'));

        $this->assertDatabaseCount('kelas_siswa', 1);
        $this->assertDatabaseHas('kelas_siswa', ['siswa_id' => $siswa->id, 'kelas_id' => $kelasB->id]);
    }

    public function test_nis_siswa_tidak_boleh_kembar(): void
    {
        $this->tahun();
        $admin = $this->admin();

        $this->actingAs($admin)->post(route('admin.siswa.store'), $this->dataSiswa());

        $this->actingAs($admin)
            ->post(route('admin.siswa.store'), $this->dataSiswa(['email' => 'lain@sekolah.test']))
            ->assertSessionHasErrors('nis');
    }

    public function test_guru_tidak_bisa_membuka_halaman_kelola_siswa(): void
    {
        $guru = User::factory()->create();
        $guru->assignRole('guru');

        $this->actingAs($guru)->get(route('admin.siswa.index'))->assertForbidden();
    }
}