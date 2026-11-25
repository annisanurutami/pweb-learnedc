<?php

namespace Tests\Feature;

use App\Models\BobotNilai;
use App\Models\Guru;
use App\Models\GuruMapel;
use App\Models\Hari;
use App\Models\JadwalPelajaran;
use App\Models\Kelas;
use App\Models\KelasSiswa;
use App\Models\MataPelajaran;
use App\Models\Nilai;
use App\Models\PengumpulanTugas;
use App\Models\PresensiSiswa;
use App\Models\Semester;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use App\Models\Tugas;
use App\Models\User;
use App\Services\PerhitunganNilai;
use App\Support\Angka;
use Database\Seeders\HariSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Tests\TestCase;

class SiswaNilaiKehadiranTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->seed(RoleSeeder::class);
        $this->seed(HariSeeder::class);
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

    private function semester(TahunAjaran $tahun): Semester
    {
        return Semester::create([
            'tahun_ajaran_id' => $tahun->id,
            'nama' => 'Ganjil',
            'tgl_mulai' => '2026-07-13',
            'tgl_selesai' => '2026-12-19',
            'is_active' => true,
        ]);
    }

    private function kelas(TahunAjaran $tahun, string $nama = 'X-A'): Kelas
    {
        return Kelas::create([
            'tahun_ajaran_id' => $tahun->id,
            'nama_kelas' => $nama,
            'iuran_kas' => 0,
        ]);
    }

    private function penugasan(Kelas $kelas, string $kode, string $namaMapel): GuruMapel
    {
        $mapel = MataPelajaran::firstOrCreate(['kode' => $kode], ['nama' => $namaMapel]);

        $akunGuru = User::factory()->create();
        $akunGuru->assignRole('guru');
        $guru = Guru::create(['user_id' => $akunGuru->id, 'nama' => 'Guru ' . $kode]);

        return GuruMapel::create([
            'kelas_id' => $kelas->id,
            'mapel_id' => $mapel->id,
            'guru_id' => $guru->id,
        ]);
    }

    private function akunSiswa(Kelas $kelas, string $nama = 'Andi', string $nis = '1001'): User
    {
        $akun = User::factory()->create(['name' => $nama]);
        $akun->assignRole('siswa');

        $siswa = Siswa::create([
            'user_id' => $akun->id,
            'nis' => $nis,
            'nama' => $nama,
            'jenis_kelamin' => 'L',
        ]);

        KelasSiswa::create([
            'kelas_id' => $kelas->id,
            'siswa_id' => $siswa->id,
            'tahun_ajaran_id' => $kelas->tahun_ajaran_id,
        ]);

        return $akun;
    }

    private function nilai(Siswa $siswa, GuruMapel $gm, Semester $semester, string $jenis, float $angka, ?int $sumber = null): Nilai
    {
        return Nilai::create([
            'siswa_id' => $siswa->id,
            'guru_mapel_id' => $gm->id,
            'semester_id' => $semester->id,
            'jenis' => $jenis,
            'nilai' => $angka,
            'sumber_id' => $sumber ?? random_int(1000, 999999),
        ]);
    }

    /** @param list<array{0: string, 1: float}> $baris */
    private function koleksiNilai(array $baris): Collection
    {
        return collect($baris)->map(fn($b) => new Nilai(['jenis' => $b[0], 'nilai' => $b[1]]));
    }

    // ------------------------------------------------------------------
    // Rumus nilai akhir
    // ------------------------------------------------------------------

    public function test_nilai_akhir_hanya_dari_tugas_jika_belum_ada_ujian(): void
    {
        $hasil = (new PerhitunganNilai())->hitung(
            $this->koleksiNilai([['tugas_harian', 80], ['tugas_harian', 90]]),
            ['tugas' => 40, 'ujian' => 60]
        );

        $this->assertSame(85.0, $hasil['tugas']);
        $this->assertNull($hasil['ujian']);
        $this->assertSame(85.0, $hasil['akhir']);
    }

    public function test_nilai_akhir_hanya_dari_ujian_jika_belum_ada_tugas(): void
    {
        $hasil = (new PerhitunganNilai())->hitung(
            $this->koleksiNilai([['UTS', 70]]),
            ['tugas' => 40, 'ujian' => 60]
        );

        $this->assertNull($hasil['tugas']);
        $this->assertSame(70.0, $hasil['akhir']);
    }

    public function test_nilai_akhir_berbobot_tugas_dan_ujian(): void
    {
        $hasil = (new PerhitunganNilai())->hitung(
            $this->koleksiNilai([['tugas_harian', 80], ['UH', 90]]),
            ['tugas' => 40, 'ujian' => 60]
        );

        // (80 x 40 + 90 x 60) / 100
        $this->assertSame(86.0, $hasil['akhir']);
    }

    public function test_nilai_ujian_adalah_rata_rata_uh_uts_dan_uas(): void
    {
        $hasil = (new PerhitunganNilai())->hitung(
            $this->koleksiNilai([['UH', 80], ['UTS', 90], ['UAS', 100]]),
            ['tugas' => 40, 'ujian' => 60]
        );

        $this->assertSame(90.0, $hasil['ujian']);
        $this->assertSame(90.0, $hasil['akhir']);
    }

    public function test_tanpa_nilai_semua_hasil_kosong(): void
    {
        $hasil = (new PerhitunganNilai())->hitung(collect(), ['tugas' => 40, 'ujian' => 60]);

        $this->assertSame(['tugas' => null, 'ujian' => null, 'akhir' => null], $hasil);
    }

    public function test_bobot_nol_memakai_rata_rata_biasa(): void
    {
        $hasil = (new PerhitunganNilai())->hitung(
            $this->koleksiNilai([['tugas_harian', 80], ['UH', 90]]),
            ['tugas' => 0, 'ujian' => 0]
        );

        $this->assertSame(85.0, $hasil['akhir']);
    }

    public function test_bobot_diambil_dari_semester_dan_ada_nilai_bawaan(): void
    {
        $semester = $this->semester($this->tahun());
        $service = new PerhitunganNilai();

        $this->assertSame(['tugas' => 40, 'ujian' => 60], $service->bobot($semester));

        BobotNilai::create(['semester_id' => $semester->id, 'bobot_tugas' => 50, 'bobot_ujian' => 50]);

        $this->assertSame(['tugas' => 50, 'ujian' => 50], $service->bobot($semester));
    }

    public function test_angka_ditampilkan_dengan_koma_dan_tanpa_nol_berlebih(): void
    {
        $this->assertSame('-', Angka::nilai(null));
        $this->assertSame('85', Angka::nilai(85.0));
        $this->assertSame('85,5', Angka::nilai(85.5));
        $this->assertSame('86,25', Angka::nilai(86.25));
        $this->assertSame('100', Angka::nilai(100.0));
        $this->assertSame('60,5', Angka::nilai(60.5, 1));
    }

    // ------------------------------------------------------------------
    // Halaman nilai siswa
    // ------------------------------------------------------------------

    public function test_guru_tidak_boleh_membuka_halaman_nilai_dan_kehadiran_siswa(): void
    {
        $guru = User::factory()->create();
        $guru->assignRole('guru');

        $this->actingAs($guru)->get(route('siswa.nilai.index'))->assertForbidden();
        $this->actingAs($guru)->get(route('siswa.kehadiran.index'))->assertForbidden();
    }

    public function test_siswa_melihat_nilai_per_mata_pelajaran_dan_rata_rata(): void
    {
        $tahun = $this->tahun();
        $semester = $this->semester($tahun);
        $kelas = $this->kelas($tahun);
        $mtk = $this->penugasan($kelas, 'MTK', 'Matematika');
        $bin = $this->penugasan($kelas, 'BIN', 'Bahasa Indonesia');
        $akun = $this->akunSiswa($kelas);

        $this->nilai($akun->siswa, $mtk, $semester, 'tugas_harian', 80);
        $this->nilai($akun->siswa, $bin, $semester, 'tugas_harian', 90);

        $this->actingAs($akun)
            ->get(route('siswa.nilai.index'))
            ->assertOk()
            ->assertSee('Matematika')
            ->assertSee('Bahasa Indonesia')
            ->assertSeeInOrder(['Rata-rata nilai', '<strong>85</strong>'], false);
    }

    public function test_nilai_ujian_ikut_dihitung_dengan_bobot(): void
    {
        $tahun = $this->tahun();
        $semester = $this->semester($tahun);
        $kelas = $this->kelas($tahun);
        $mtk = $this->penugasan($kelas, 'MTK', 'Matematika');
        $akun = $this->akunSiswa($kelas);

        $this->nilai($akun->siswa, $mtk, $semester, 'tugas_harian', 80);
        $this->nilai($akun->siswa, $mtk, $semester, 'UH', 90);

        $this->actingAs($akun)
            ->get(route('siswa.nilai.index'))
            ->assertOk()
            ->assertSee('<td class="px-4 py-3 font-bold">86</td>', false)
            ->assertSee('tugas 40% + ujian 60%');
    }

    public function test_nilai_siswa_lain_tidak_ikut_terlihat_atau_terhitung(): void
    {
        $tahun = $this->tahun();
        $semester = $this->semester($tahun);
        $kelas = $this->kelas($tahun);
        $mtk = $this->penugasan($kelas, 'MTK', 'Matematika');
        $andi = $this->akunSiswa($kelas, 'Andi', '1001');
        $budi = $this->akunSiswa($kelas, 'Budi', '1002');

        $this->nilai($andi->siswa, $mtk, $semester, 'tugas_harian', 70);
        $this->nilai($budi->siswa, $mtk, $semester, 'tugas_harian', 99);

        $this->actingAs($andi)
            ->get(route('siswa.nilai.index'))
            ->assertSee('<td class="px-4 py-3 font-bold">70</td>', false)
            ->assertDontSee('99');
    }

    public function test_nilai_semester_lain_tidak_dihitung(): void
    {
        $tahun = $this->tahun();
        $semester = $this->semester($tahun);
        $lama = Semester::create([
            'tahun_ajaran_id' => $tahun->id,
            'nama' => 'Genap',
            'tgl_mulai' => '2027-01-04',
            'tgl_selesai' => '2027-06-26',
            'is_active' => false,
        ]);
        $kelas = $this->kelas($tahun);
        $mtk = $this->penugasan($kelas, 'MTK', 'Matematika');
        $akun = $this->akunSiswa($kelas);

        $this->nilai($akun->siswa, $mtk, $semester, 'tugas_harian', 80);
        $this->nilai($akun->siswa, $mtk, $lama, 'tugas_harian', 10);

        $this->actingAs($akun)
            ->get(route('siswa.nilai.index'))
            ->assertSee('<td class="px-4 py-3 font-bold">80</td>', false);
    }

    public function test_mata_pelajaran_tanpa_nilai_tampil_strip(): void
    {
        $tahun = $this->tahun();
        $this->semester($tahun);
        $kelas = $this->kelas($tahun);
        $this->penugasan($kelas, 'MTK', 'Matematika');
        $akun = $this->akunSiswa($kelas);

        $this->actingAs($akun)
            ->get(route('siswa.nilai.index'))
            ->assertOk()
            ->assertSee('Matematika')
            ->assertSee('<td class="px-4 py-3 font-bold">-</td>', false);
    }

    public function test_siswa_tanpa_kelas_melihat_pemberitahuan_di_halaman_nilai(): void
    {
        $tahun = $this->tahun();
        $this->semester($tahun);
        $akun = User::factory()->create();
        $akun->assignRole('siswa');
        Siswa::create(['user_id' => $akun->id, 'nis' => '9', 'nama' => 'Tanpa Kelas', 'jenis_kelamin' => 'L']);

        $this->actingAs($akun)->get(route('siswa.nilai.index'))->assertOk()->assertSee('belum terdaftar di kelas');
        $this->actingAs($akun)->get(route('siswa.kehadiran.index'))->assertOk()->assertSee('belum terdaftar di kelas');
    }

    // ------------------------------------------------------------------
    // Halaman kehadiran siswa
    // ------------------------------------------------------------------

    private function presensi(Siswa $siswa, Kelas $kelas, string $tanggal, string $status, ?int $jadwalId = null, ?string $ket = null): PresensiSiswa
    {
        return PresensiSiswa::create([
            'siswa_id' => $siswa->id,
            'kelas_id' => $kelas->id,
            'jadwal_id' => $jadwalId,
            'tanggal' => $tanggal,
            'status' => $status,
            'keterangan' => $ket,
        ]);
    }

    public function test_rekap_kehadiran_menghitung_persentase_hadir(): void
    {
        $tahun = $this->tahun();
        $this->semester($tahun);
        $kelas = $this->kelas($tahun);
        $akun = $this->akunSiswa($kelas);
        $siswa = $akun->siswa;

        $this->presensi($siswa, $kelas, '2026-09-07', 'H');
        $this->presensi($siswa, $kelas, '2026-09-08', 'H');
        $this->presensi($siswa, $kelas, '2026-09-09', 'H');
        $this->presensi($siswa, $kelas, '2026-09-10', 'S', null, 'Demam');
        $this->presensi($siswa, $kelas, '2026-09-11', 'A');

        $this->actingAs($akun)
            ->get(route('siswa.kehadiran.index'))
            ->assertOk()
            ->assertSeeInOrder(['Persentase hadir', '<strong>60%</strong>', '5 pertemuan tercatat'], false)
            ->assertSee('Demam')
            ->assertSee('Absensi harian kelas');
    }

    public function test_riwayat_kehadiran_menampilkan_nama_mata_pelajaran_dari_jadwal(): void
    {
        $tahun = $this->tahun();
        $semester = $this->semester($tahun);
        $kelas = $this->kelas($tahun);
        $mtk = $this->penugasan($kelas, 'MTK', 'Matematika');
        $akun = $this->akunSiswa($kelas);

        $jadwal = JadwalPelajaran::create([
            'guru_mapel_id' => $mtk->id,
            'semester_id' => $semester->id,
            'hari_id' => Hari::where('urutan', 1)->value('id'),
            'jam_mulai' => '07:30:00',
            'jam_selesai' => '09:00:00',
        ]);
        $this->presensi($akun->siswa, $kelas, '2026-09-07', 'H', $jadwal->id);

        $this->actingAs($akun)
            ->get(route('siswa.kehadiran.index'))
            ->assertOk()
            ->assertSee('Matematika')
            ->assertSee('07-09-2026');
    }

    public function test_kehadiran_di_luar_semester_dan_milik_siswa_lain_tidak_dihitung(): void
    {
        $tahun = $this->tahun();
        $this->semester($tahun);
        $kelas = $this->kelas($tahun);
        $andi = $this->akunSiswa($kelas, 'Andi', '1001');
        $budi = $this->akunSiswa($kelas, 'Budi', '1002');

        $this->presensi($andi->siswa, $kelas, '2026-09-07', 'H');
        $this->presensi($andi->siswa, $kelas, '2027-02-01', 'A'); // di luar rentang semester aktif
        $this->presensi($budi->siswa, $kelas, '2026-09-07', 'A');

        $this->actingAs($andi)
            ->get(route('siswa.kehadiran.index'))
            ->assertSeeInOrder(['Persentase hadir', '<strong>100%</strong>', '1 pertemuan tercatat'], false);
    }

    public function test_tanpa_data_kehadiran_tampil_pesan_kosong(): void
    {
        $tahun = $this->tahun();
        $this->semester($tahun);
        $kelas = $this->kelas($tahun);
        $akun = $this->akunSiswa($kelas);

        $this->actingAs($akun)
            ->get(route('siswa.kehadiran.index'))
            ->assertOk()
            ->assertSee('Belum ada data kehadiran')
            ->assertSeeInOrder(['Persentase hadir', '<strong>-</strong>'], false);
    }

    // ------------------------------------------------------------------
    // Dashboard dan menu siswa
    // ------------------------------------------------------------------

    public function test_dashboard_siswa_menampilkan_kehadiran_nilai_dan_tugas_terbuka(): void
    {
        $tahun = $this->tahun();
        $semester = $this->semester($tahun);
        $kelas = $this->kelas($tahun);
        $mtk = $this->penugasan($kelas, 'MTK', 'Matematika');
        $akun = $this->akunSiswa($kelas);
        $siswa = $akun->siswa;

        $this->presensi($siswa, $kelas, '2026-09-07', 'H');
        $this->presensi($siswa, $kelas, '2026-09-08', 'S');
        $this->nilai($siswa, $mtk, $semester, 'tugas_harian', 88);

        $terbuka = Tugas::create([
            'guru_mapel_id' => $mtk->id,
            'semester_id' => $semester->id,
            'judul' => 'Tugas Terbuka',
            'deadline' => now()->addDays(2),
        ]);
        Tugas::create([
            'guru_mapel_id' => $mtk->id,
            'semester_id' => $semester->id,
            'judul' => 'Tugas Lama',
            'deadline' => now()->subDay(),
        ]);

        $this->actingAs($akun)
            ->get(route('siswa.dashboard'))
            ->assertOk()
            ->assertSeeInOrder(['Kehadiran', '<strong>50%</strong>'], false)
            ->assertSeeInOrder(['Rata-rata nilai', '<strong>88</strong>'], false)
            ->assertSeeInOrder(['Tugas terbuka', '<strong>1</strong>'], false);

        // Setelah dikumpulkan, tidak lagi dihitung sebagai tugas terbuka.
        PengumpulanTugas::create([
            'tugas_id' => $terbuka->id,
            'siswa_id' => $siswa->id,
            'submitted_at' => now(),
        ]);

        $this->actingAs($akun)
            ->get(route('siswa.dashboard'))
            ->assertSeeInOrder(['Tugas terbuka', '<strong>0</strong>'], false);
    }

    public function test_dashboard_siswa_tanpa_data_siswa_tetap_terbuka(): void
    {
        $akun = User::factory()->create();
        $akun->assignRole('siswa');

        $this->actingAs($akun)->get(route('siswa.dashboard'))->assertOk();
    }

    public function test_sidebar_siswa_memuat_menu_kehadiran_dan_nilai(): void
    {
        $tahun = $this->tahun();
        $this->semester($tahun);
        $kelas = $this->kelas($tahun);
        $akun = $this->akunSiswa($kelas);

        $this->actingAs($akun)
            ->get(route('siswa.dashboard'))
            ->assertOk()
            ->assertSee(route('siswa.kehadiran.index'), false)
            ->assertSee(route('siswa.nilai.index'), false);
    }
}