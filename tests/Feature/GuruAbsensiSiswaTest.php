<?php

namespace Tests\Feature;

use App\Models\Guru;
use App\Models\GuruMapel;
use App\Models\Hari;
use App\Models\JadwalPelajaran;
use App\Models\Kelas;
use App\Models\KelasSiswa;
use App\Models\MataPelajaran;
use App\Models\PresensiSiswa;
use App\Models\Semester;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\HariSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GuruAbsensiSiswaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->seed(RoleSeeder::class);
        $this->seed(HariSeeder::class);
    }

    private function akunGuru(string $nama): User
    {
        $user = User::factory()->create();
        $user->assignRole('guru');
        Guru::create(['user_id' => $user->id, 'nama' => $nama]);

        return $user;
    }

    private function tahun(): TahunAjaran
    {
        return TahunAjaran::create([
            'nama' => '2026/2027',
            'tgl_mulai' => now()->subMonths(2)->toDateString(),
            'tgl_selesai' => now()->addMonths(6)->toDateString(),
            'is_active' => true,
        ]);
    }

    /** Semester aktif yang mencakup dua bulan lalu sampai dua bulan ke depan. */
    private function semester(TahunAjaran $tahun): Semester
    {
        return Semester::create([
            'tahun_ajaran_id' => $tahun->id,
            'nama' => 'Ganjil',
            'tgl_mulai' => now()->subMonths(2)->toDateString(),
            'tgl_selesai' => now()->addMonths(2)->toDateString(),
            'is_active' => true,
        ]);
    }

    private function penugasan(TahunAjaran $tahun, User $akunGuru, string $namaKelas = 'X-A', string $kode = 'MTK'): GuruMapel
    {
        $kelas = Kelas::create([
            'tahun_ajaran_id' => $tahun->id,
            'nama_kelas' => $namaKelas,
            'iuran_kas' => 0,
        ]);
        $mapel = MataPelajaran::create(['kode' => $kode, 'nama' => 'Mapel '.$kode]);

        return GuruMapel::create([
            'kelas_id' => $kelas->id,
            'mapel_id' => $mapel->id,
            'guru_id' => Guru::where('user_id', $akunGuru->id)->value('id'),
        ]);
    }

    /** Jadwal yang hari-nya sama dengan hari pada $tanggal. */
    private function jadwal(GuruMapel $penugasan, Semester $semester, Carbon $tanggal): JadwalPelajaran
    {
        return JadwalPelajaran::create([
            'guru_mapel_id' => $penugasan->id,
            'semester_id' => $semester->id,
            'hari_id' => Hari::where('urutan', $tanggal->dayOfWeekIso)->value('id'),
            'jam_mulai' => '07:30:00',
            'jam_selesai' => '09:00:00',
        ]);
    }

    private function siswa(GuruMapel $penugasan, string $nama, string $nis): Siswa
    {
        $akun = User::factory()->create();
        $akun->assignRole('siswa');

        $siswa = Siswa::create([
            'user_id' => $akun->id,
            'nis' => $nis,
            'nama' => $nama,
            'jenis_kelamin' => 'L',
        ]);

        KelasSiswa::create([
            'kelas_id' => $penugasan->kelas_id,
            'siswa_id' => $siswa->id,
            'tahun_ajaran_id' => $penugasan->kelas->tahun_ajaran_id,
        ]);

        return $siswa;
    }

    /**
     * @param  array<int, string>  $status  siswa_id => kode status
     * @param  array<int, string>  $keterangan
     * @return array<string, mixed>
     */
    private function isian(JadwalPelajaran $jadwal, string $tanggal, array $status, array $keterangan = []): array
    {
        return [
            'jadwal_id' => $jadwal->id,
            'tanggal' => $tanggal,
            'status' => $status,
            'keterangan' => $keterangan,
        ];
    }

    public function test_siswa_tidak_boleh_membuka_absensi(): void
    {
        $tahun = $this->tahun();
        $penugasan = $this->penugasan($tahun, $this->akunGuru('Bu Ratna'));
        $akun = User::factory()->create();
        $akun->assignRole('siswa');

        $this->actingAs($akun)
            ->get(route('guru.absensi.index', ['penugasan' => $penugasan]))
            ->assertForbidden();
    }

    public function test_tanpa_pertemuan_terpilih_hanya_tampil_pilihan_jadwal(): void
    {
        $tahun = $this->tahun();
        $semester = $this->semester($tahun);
        $guru = $this->akunGuru('Bu Ratna');
        $penugasan = $this->penugasan($tahun, $guru);
        $this->jadwal($penugasan, $semester, now()->subWeek());
        $this->siswa($penugasan, 'Andi', '1001');

        $this->actingAs($guru)
            ->get(route('guru.absensi.index', ['penugasan' => $penugasan]))
            ->assertOk()
            ->assertSee('Tampilkan')
            ->assertSee('Belum ada pertemuan yang diabsen')
            ->assertDontSee('Simpan absensi');
    }

    public function test_guru_melihat_siswa_kelas_pada_pertemuan_terpilih(): void
    {
        $tahun = $this->tahun();
        $semester = $this->semester($tahun);
        $guru = $this->akunGuru('Bu Ratna');
        $penugasan = $this->penugasan($tahun, $guru);
        $tanggal = now()->subWeek();
        $jadwal = $this->jadwal($penugasan, $semester, $tanggal);
        $this->siswa($penugasan, 'Andi', '1001');
        $this->siswa($penugasan, 'Budi', '1002');

        $this->actingAs($guru)
            ->get(route('guru.absensi.index', [
                'penugasan' => $penugasan,
                'jadwal_id' => $jadwal->id,
                'tanggal' => $tanggal->format('Y-m-d'),
            ]))
            ->assertOk()
            ->assertSee('Andi')
            ->assertSee('Budi')
            ->assertSee('Simpan absensi');
    }

    public function test_guru_menyimpan_absensi_seluruh_siswa(): void
    {
        $tahun = $this->tahun();
        $semester = $this->semester($tahun);
        $guru = $this->akunGuru('Bu Ratna');
        $penugasan = $this->penugasan($tahun, $guru);
        $tanggal = now()->subWeek();
        $tgl = $tanggal->format('Y-m-d');
        $jadwal = $this->jadwal($penugasan, $semester, $tanggal);
        $andi = $this->siswa($penugasan, 'Andi', '1001');
        $budi = $this->siswa($penugasan, 'Budi', '1002');
        $citra = $this->siswa($penugasan, 'Citra', '1003');

        $this->actingAs($guru)
            ->put(route('guru.absensi.update', ['penugasan' => $penugasan]), $this->isian(
                $jadwal,
                $tgl,
                [$andi->id => 'H', $budi->id => 'S', $citra->id => 'I'],
                [$andi->id => 'tidak dipakai', $budi->id => 'Demam', $citra->id => 'Acara keluarga'],
            ))
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('guru.absensi.index', [
                'penugasan' => $penugasan,
                'jadwal_id' => $jadwal->id,
                'tanggal' => $tgl,
            ]));

        $this->assertSame(3, PresensiSiswa::whereDate('tanggal', $tgl)->count());

        $baris = PresensiSiswa::where('siswa_id', $budi->id)->firstOrFail();
        $this->assertSame('S', $baris->status);
        $this->assertSame('Demam', $baris->keterangan);
        $this->assertSame($penugasan->kelas_id, (int) $baris->kelas_id);
        $this->assertSame($jadwal->id, (int) $baris->jadwal_id);
        $this->assertSame($guru->id, (int) $baris->dicatat_oleh);

        // Keterangan untuk siswa yang hadir tidak disimpan.
        $this->assertNull(PresensiSiswa::where('siswa_id', $andi->id)->firstOrFail()->keterangan);
    }

    public function test_menyimpan_ulang_pertemuan_yang_sama_memperbarui_baris_yang_ada(): void
    {
        $tahun = $this->tahun();
        $semester = $this->semester($tahun);
        $guru = $this->akunGuru('Bu Ratna');
        $penugasan = $this->penugasan($tahun, $guru);
        $tanggal = now()->subWeek();
        $tgl = $tanggal->format('Y-m-d');
        $jadwal = $this->jadwal($penugasan, $semester, $tanggal);
        $andi = $this->siswa($penugasan, 'Andi', '1001');
        $budi = $this->siswa($penugasan, 'Budi', '1002');
        $url = route('guru.absensi.update', ['penugasan' => $penugasan]);

        $this->actingAs($guru)->put($url, $this->isian($jadwal, $tgl, [$andi->id => 'H', $budi->id => 'H']));
        $this->actingAs($guru)->put($url, $this->isian($jadwal, $tgl, [$andi->id => 'A', $budi->id => 'H'], [$andi->id => 'Tanpa kabar']));

        $this->assertSame(2, PresensiSiswa::count());

        $baris = PresensiSiswa::where('siswa_id', $andi->id)->firstOrFail();
        $this->assertSame('A', $baris->status);
        $this->assertSame('Tanpa kabar', $baris->keterangan);
    }

    public function test_status_yang_tidak_valid_ditolak(): void
    {
        $tahun = $this->tahun();
        $semester = $this->semester($tahun);
        $guru = $this->akunGuru('Bu Ratna');
        $penugasan = $this->penugasan($tahun, $guru);
        $tanggal = now()->subWeek();
        $jadwal = $this->jadwal($penugasan, $semester, $tanggal);
        $andi = $this->siswa($penugasan, 'Andi', '1001');

        $this->actingAs($guru)
            ->put(route('guru.absensi.update', ['penugasan' => $penugasan]), $this->isian(
                $jadwal,
                $tanggal->format('Y-m-d'),
                [$andi->id => 'X'],
            ))
            ->assertSessionHasErrors('status.'.$andi->id);

        $this->assertDatabaseCount('presensi_siswa', 0);
    }

    public function test_semua_siswa_kelas_harus_diisi(): void
    {
        $tahun = $this->tahun();
        $semester = $this->semester($tahun);
        $guru = $this->akunGuru('Bu Ratna');
        $penugasan = $this->penugasan($tahun, $guru);
        $tanggal = now()->subWeek();
        $jadwal = $this->jadwal($penugasan, $semester, $tanggal);
        $andi = $this->siswa($penugasan, 'Andi', '1001');
        $this->siswa($penugasan, 'Budi', '1002');

        $this->actingAs($guru)
            ->put(route('guru.absensi.update', ['penugasan' => $penugasan]), $this->isian(
                $jadwal,
                $tanggal->format('Y-m-d'),
                [$andi->id => 'H'],
            ))
            ->assertSessionHasErrors('status');

        $this->assertDatabaseCount('presensi_siswa', 0);
    }

    public function test_siswa_kelas_lain_tidak_bisa_ikut_diabsen(): void
    {
        $tahun = $this->tahun();
        $semester = $this->semester($tahun);
        $guru = $this->akunGuru('Bu Ratna');
        $penugasanA = $this->penugasan($tahun, $guru, 'X-A', 'MTK');
        $penugasanB = $this->penugasan($tahun, $guru, 'X-B', 'BIN');
        $tanggal = now()->subWeek();
        $jadwal = $this->jadwal($penugasanA, $semester, $tanggal);
        $andi = $this->siswa($penugasanA, 'Andi', '1001');
        $dewi = $this->siswa($penugasanB, 'Dewi', '2001');

        $this->actingAs($guru)
            ->put(route('guru.absensi.update', ['penugasan' => $penugasanA]), $this->isian(
                $jadwal,
                $tanggal->format('Y-m-d'),
                [$andi->id => 'H', $dewi->id => 'H'],
            ))
            ->assertSessionHasErrors('status');

        $this->assertDatabaseCount('presensi_siswa', 0);
    }

    public function test_tanggal_format_salah_ditolak(): void
    {
        $tahun = $this->tahun();
        $semester = $this->semester($tahun);
        $guru = $this->akunGuru('Bu Ratna');
        $penugasan = $this->penugasan($tahun, $guru);
        $jadwal = $this->jadwal($penugasan, $semester, now()->subWeek());
        $andi = $this->siswa($penugasan, 'Andi', '1001');

        $this->actingAs($guru)
            ->put(route('guru.absensi.update', ['penugasan' => $penugasan]), $this->isian(
                $jadwal,
                'besok',
                [$andi->id => 'H'],
            ))
            ->assertSessionHasErrors('tanggal');

        $this->assertDatabaseCount('presensi_siswa', 0);
    }

    public function test_tanggal_di_masa_depan_ditolak(): void
    {
        $tahun = $this->tahun();
        $semester = $this->semester($tahun);
        $guru = $this->akunGuru('Bu Ratna');
        $penugasan = $this->penugasan($tahun, $guru);
        $depan = now()->addWeek();
        $jadwal = $this->jadwal($penugasan, $semester, $depan);
        $andi = $this->siswa($penugasan, 'Andi', '1001');

        $this->actingAs($guru)
            ->put(route('guru.absensi.update', ['penugasan' => $penugasan]), $this->isian(
                $jadwal,
                $depan->format('Y-m-d'),
                [$andi->id => 'H'],
            ))
            ->assertSessionHasErrors('tanggal');

        $this->assertDatabaseCount('presensi_siswa', 0);
    }

    public function test_tanggal_di_luar_semester_aktif_ditolak(): void
    {
        $tahun = $this->tahun();
        $semester = $this->semester($tahun);
        $guru = $this->akunGuru('Bu Ratna');
        $penugasan = $this->penugasan($tahun, $guru);
        $jauh = now()->subMonths(5);
        $jadwal = $this->jadwal($penugasan, $semester, $jauh);
        $andi = $this->siswa($penugasan, 'Andi', '1001');

        $this->actingAs($guru)
            ->put(route('guru.absensi.update', ['penugasan' => $penugasan]), $this->isian(
                $jadwal,
                $jauh->format('Y-m-d'),
                [$andi->id => 'H'],
            ))
            ->assertSessionHasErrors('tanggal');

        $this->assertDatabaseCount('presensi_siswa', 0);
    }

    public function test_tanggal_harus_sesuai_hari_jadwal(): void
    {
        $tahun = $this->tahun();
        $semester = $this->semester($tahun);
        $guru = $this->akunGuru('Bu Ratna');
        $penugasan = $this->penugasan($tahun, $guru);
        $tanggal = now()->subWeek();
        $jadwal = $this->jadwal($penugasan, $semester, $tanggal);
        $andi = $this->siswa($penugasan, 'Andi', '1001');

        // Sehari setelah hari jadwal: harinya berbeda.
        $this->actingAs($guru)
            ->put(route('guru.absensi.update', ['penugasan' => $penugasan]), $this->isian(
                $jadwal,
                $tanggal->copy()->addDay()->format('Y-m-d'),
                [$andi->id => 'H'],
            ))
            ->assertSessionHasErrors('tanggal');

        $this->assertDatabaseCount('presensi_siswa', 0);
    }

    public function test_jadwal_kelas_lain_ditolak(): void
    {
        $tahun = $this->tahun();
        $semester = $this->semester($tahun);
        $guru = $this->akunGuru('Bu Ratna');
        $penugasanA = $this->penugasan($tahun, $guru, 'X-A', 'MTK');
        $penugasanB = $this->penugasan($tahun, $guru, 'X-B', 'BIN');
        $tanggal = now()->subWeek();
        $jadwalB = $this->jadwal($penugasanB, $semester, $tanggal);
        $andi = $this->siswa($penugasanA, 'Andi', '1001');

        $this->actingAs($guru)
            ->put(route('guru.absensi.update', ['penugasan' => $penugasanA]), $this->isian(
                $jadwalB,
                $tanggal->format('Y-m-d'),
                [$andi->id => 'H'],
            ))
            ->assertSessionHasErrors('jadwal_id');

        $this->assertDatabaseCount('presensi_siswa', 0);
    }

    public function test_tanpa_semester_aktif_absensi_ditolak(): void
    {
        $tahun = $this->tahun();
        $guru = $this->akunGuru('Bu Ratna');
        $penugasan = $this->penugasan($tahun, $guru);

        $this->actingAs($guru)
            ->put(route('guru.absensi.update', ['penugasan' => $penugasan]), [
                'jadwal_id' => 1,
                'tanggal' => now()->subWeek()->format('Y-m-d'),
                'status' => [1 => 'H'],
            ])
            ->assertSessionHas('error');

        $this->assertDatabaseCount('presensi_siswa', 0);
    }

    public function test_guru_lain_tidak_bisa_mengabsen_kelas_orang(): void
    {
        $tahun = $this->tahun();
        $semester = $this->semester($tahun);
        $ratna = $this->akunGuru('Bu Ratna');
        $agus = $this->akunGuru('Pak Agus');
        $penugasan = $this->penugasan($tahun, $ratna);
        $tanggal = now()->subWeek();
        $jadwal = $this->jadwal($penugasan, $semester, $tanggal);
        $andi = $this->siswa($penugasan, 'Andi', '1001');

        $this->actingAs($agus)
            ->get(route('guru.absensi.index', ['penugasan' => $penugasan]))
            ->assertForbidden();

        $this->actingAs($agus)
            ->put(route('guru.absensi.update', ['penugasan' => $penugasan]), $this->isian(
                $jadwal,
                $tanggal->format('Y-m-d'),
                [$andi->id => 'H'],
            ))
            ->assertForbidden();

        $this->assertDatabaseCount('presensi_siswa', 0);
    }

    public function test_rekap_menampilkan_pertemuan_yang_sudah_diabsen(): void
    {
        $tahun = $this->tahun();
        $semester = $this->semester($tahun);
        $guru = $this->akunGuru('Bu Ratna');
        $penugasan = $this->penugasan($tahun, $guru);
        $tanggal = now()->subWeek();
        $jadwal = $this->jadwal($penugasan, $semester, $tanggal);
        $andi = $this->siswa($penugasan, 'Andi', '1001');
        $budi = $this->siswa($penugasan, 'Budi', '1002');

        $this->actingAs($guru)->put(
            route('guru.absensi.update', ['penugasan' => $penugasan]),
            $this->isian($jadwal, $tanggal->format('Y-m-d'), [$andi->id => 'H', $budi->id => 'A'])
        );

        $this->actingAs($guru)
            ->get(route('guru.absensi.index', ['penugasan' => $penugasan]))
            ->assertOk()
            ->assertDontSee('Belum ada pertemuan yang diabsen')
            ->assertSee($tanggal->format('d-m-Y'));
    }
}
