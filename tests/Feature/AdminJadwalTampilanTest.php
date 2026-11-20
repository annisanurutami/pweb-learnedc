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

class AdminJadwalTampilanTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->seed(RoleSeeder::class);
        $this->seed(HariSeeder::class);
    }

    private function admin(): User
    {
        $user = User::factory()->create();
        $user->assignRole('super_admin');

        return $user;
    }

    /** Membuat satu jadwal lengkap di semester aktif dan mengembalikannya. */
    private function jadwalContoh(): JadwalPelajaran
    {
        $tahun = TahunAjaran::create([
            'nama' => '2026/2027',
            'tgl_mulai' => '2026-07-13',
            'tgl_selesai' => '2027-06-26',
            'is_active' => true,
        ]);
        $semester = Semester::create([
            'tahun_ajaran_id' => $tahun->id,
            'nama' => 'Ganjil',
            'tgl_mulai' => '2026-07-13',
            'tgl_selesai' => '2026-12-19',
            'is_active' => true,
        ]);
        $kelas = Kelas::create([
            'tahun_ajaran_id' => $tahun->id,
            'nama_kelas' => 'X-A',
            'iuran_kas' => 0,
        ]);

        $user = User::factory()->create();
        $user->assignRole('guru');
        $guru = Guru::create(['user_id' => $user->id, 'nama' => 'Bu Ratna']);

        $mapel = MataPelajaran::create(['kode' => 'MTK', 'nama' => 'Matematika']);
        $gm = GuruMapel::create([
            'kelas_id' => $kelas->id,
            'mapel_id' => $mapel->id,
            'guru_id' => $guru->id,
        ]);

        return JadwalPelajaran::create([
            'guru_mapel_id' => $gm->id,
            'semester_id' => $semester->id,
            'hari_id' => Hari::where('nama_hari', 'Senin')->value('id'),
            'jam_mulai' => '07:30:00',
            'jam_selesai' => '09:00:00',
        ]);
    }

    public function test_daftar_jadwal_menampilkan_isi_jadwal(): void
    {
        $this->jadwalContoh();

        $this->actingAs($this->admin())
            ->get(route('admin.jadwal.index'))
            ->assertOk()
            ->assertSee('Jadwal Pelajaran')
            ->assertSee('Matematika')
            ->assertSee('Bu Ratna')
            ->assertSee('07:30 - 09:00');
    }

    public function test_daftar_jadwal_memberi_peringatan_tanpa_semester_aktif(): void
    {
        $this->actingAs($this->admin())
            ->get(route('admin.jadwal.index'))
            ->assertOk()
            ->assertSee('Belum ada semester aktif');
    }

    public function test_form_tambah_jadwal_tampil(): void
    {
        $this->jadwalContoh();

        $this->actingAs($this->admin())
            ->get(route('admin.jadwal.create'))
            ->assertOk()
            ->assertSee('Jam mulai')
            ->assertSee('X-A - Matematika - Bu Ratna');
    }

    public function test_form_ubah_jadwal_tampil_dengan_jam_terisi(): void
    {
        $jadwal = $this->jadwalContoh();

        $this->actingAs($this->admin())
            ->get(route('admin.jadwal.edit', $jadwal))
            ->assertOk()
            ->assertSee('Ubah Jadwal')
            ->assertSee('value="07:30"', false);
    }
}