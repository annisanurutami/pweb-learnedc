<?php

namespace Tests\Feature;

use App\Models\AuditNilai;
use App\Models\Guru;
use App\Models\GuruMapel;
use App\Models\Kelas;
use App\Models\KelasSiswa;
use App\Models\MataPelajaran;
use App\Models\Nilai;
use App\Models\PengumpulanTugas;
use App\Models\Semester;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use App\Models\Tugas;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class GuruNilaiTugasTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->seed(RoleSeeder::class);
        Storage::fake('local');
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
            'tgl_mulai' => '2026-07-13',
            'tgl_selesai' => '2027-06-26',
            'is_active' => true,
        ]);
    }

    private function semester(TahunAjaran $tahun, string $nama = 'Ganjil', bool $aktif = true): Semester
    {
        return Semester::create([
            'tahun_ajaran_id' => $tahun->id,
            'nama' => $nama,
            'tgl_mulai' => '2026-07-13',
            'tgl_selesai' => '2026-12-19',
            'is_active' => $aktif,
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

    /** @param array<string, mixed> $atribut */
    private function tugas(GuruMapel $penugasan, Semester $semester, array $atribut = []): Tugas
    {
        return Tugas::create($atribut + [
            'guru_mapel_id' => $penugasan->id,
            'semester_id' => $semester->id,
            'judul' => 'Tugas 1',
            'deskripsi' => null,
            'file_path' => null,
            'deadline' => now()->addDays(3),
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

    /** @return array<string, mixed> */
    private function par(GuruMapel $penugasan, Tugas $tugas): array
    {
        return ['penugasan' => $penugasan, 'tugas' => $tugas];
    }

    public function test_siswa_tidak_boleh_membuka_halaman_nilai(): void
    {
        $tahun = $this->tahun();
        $semester = $this->semester($tahun);
        $penugasan = $this->penugasan($tahun, $this->akunGuru('Bu Ratna'));
        $tugas = $this->tugas($penugasan, $semester);
        $akun = User::factory()->create();
        $akun->assignRole('siswa');

        $this->actingAs($akun)
            ->get(route('guru.nilai.index', $this->par($penugasan, $tugas)))
            ->assertForbidden();
    }

    public function test_guru_melihat_siswa_kelas_dengan_status_pengumpulan(): void
    {
        $tahun = $this->tahun();
        $semester = $this->semester($tahun);
        $guru = $this->akunGuru('Bu Ratna');
        $penugasan = $this->penugasan($tahun, $guru);
        $tugas = $this->tugas($penugasan, $semester);
        $andi = $this->siswa($penugasan, 'Andi', '1001');
        $budi = $this->siswa($penugasan, 'Budi', '1002');
        $this->siswa($penugasan, 'Citra', '1003');

        PengumpulanTugas::create([
            'tugas_id' => $tugas->id,
            'siswa_id' => $andi->id,
            'submitted_at' => $tugas->deadline->copy()->subHour(),
        ]);
        PengumpulanTugas::create([
            'tugas_id' => $tugas->id,
            'siswa_id' => $budi->id,
            'submitted_at' => $tugas->deadline->copy()->addHour(),
        ]);

        $this->actingAs($guru)
            ->get(route('guru.nilai.index', $this->par($penugasan, $tugas)))
            ->assertOk()
            ->assertSee('Andi')
            ->assertSee('Budi')
            ->assertSee('Citra')
            ->assertSee('Tepat waktu')
            ->assertSee('Terlambat')
            ->assertSee('Belum mengumpulkan');
    }

    public function test_guru_menginput_nilai_beberapa_siswa_sekaligus(): void
    {
        $tahun = $this->tahun();
        $semester = $this->semester($tahun);
        $guru = $this->akunGuru('Bu Ratna');
        $penugasan = $this->penugasan($tahun, $guru);
        $tugas = $this->tugas($penugasan, $semester);
        $andi = $this->siswa($penugasan, 'Andi', '1001');
        $budi = $this->siswa($penugasan, 'Budi', '1002');

        $this->actingAs($guru)
            ->put(route('guru.nilai.update', $this->par($penugasan, $tugas)), [
                'nilai' => [$andi->id => '85', $budi->id => '70.5'],
                'catatan' => [$andi->id => 'Bagus'],
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('guru.nilai.index', $this->par($penugasan, $tugas)));

        $nilaiAndi = Nilai::where('siswa_id', $andi->id)->firstOrFail();
        $this->assertSame(85.0, (float) $nilaiAndi->nilai);
        $this->assertSame('tugas_harian', $nilaiAndi->jenis);
        $this->assertSame($tugas->id, (int) $nilaiAndi->sumber_id);
        $this->assertSame($penugasan->id, (int) $nilaiAndi->guru_mapel_id);
        $this->assertSame($semester->id, (int) $nilaiAndi->semester_id);

        $nilaiBudi = Nilai::where('siswa_id', $budi->id)->firstOrFail();
        $this->assertSame(70.5, (float) $nilaiBudi->nilai);

        $pengumpulanAndi = PengumpulanTugas::where('siswa_id', $andi->id)->firstOrFail();
        $this->assertSame(85.0, (float) $pengumpulanAndi->nilai);
        $this->assertSame('Bagus', $pengumpulanAndi->catatan_guru);

        $this->assertSame(2, AuditNilai::count());
        $audit = AuditNilai::where('nilai_id', $nilaiAndi->id)->firstOrFail();
        $this->assertNull($audit->nilai_lama);
        $this->assertSame(85.0, (float) $audit->nilai_baru);
        $this->assertSame($guru->id, (int) $audit->user_id);
    }

    public function test_siswa_yang_belum_mengumpulkan_tetap_bisa_dinilai(): void
    {
        $tahun = $this->tahun();
        $semester = $this->semester($tahun);
        $guru = $this->akunGuru('Bu Ratna');
        $penugasan = $this->penugasan($tahun, $guru);
        $tugas = $this->tugas($penugasan, $semester);
        $budi = $this->siswa($penugasan, 'Budi', '1002');

        $this->actingAs($guru)
            ->put(route('guru.nilai.update', $this->par($penugasan, $tugas)), [
                'nilai' => [$budi->id => '60'],
            ])
            ->assertSessionHasNoErrors();

        $pengumpulan = PengumpulanTugas::where('tugas_id', $tugas->id)->where('siswa_id', $budi->id)->firstOrFail();
        $this->assertNull($pengumpulan->submitted_at);
        $this->assertSame(60.0, (float) $pengumpulan->nilai);
        $this->assertSame(1, Nilai::count());
    }

    public function test_mengedit_nilai_mengubah_baris_yang_sama_dan_mencatat_audit(): void
    {
        $tahun = $this->tahun();
        $semester = $this->semester($tahun);
        $guru = $this->akunGuru('Bu Ratna');
        $penugasan = $this->penugasan($tahun, $guru);
        $tugas = $this->tugas($penugasan, $semester);
        $andi = $this->siswa($penugasan, 'Andi', '1001');
        $url = route('guru.nilai.update', $this->par($penugasan, $tugas));

        $this->actingAs($guru)->put($url, ['nilai' => [$andi->id => '85']])->assertSessionHasNoErrors();
        $this->actingAs($guru)->put($url, ['nilai' => [$andi->id => '90']])->assertSessionHasNoErrors();

        $this->assertSame(1, Nilai::count());
        $this->assertSame(90.0, (float) Nilai::firstOrFail()->nilai);
        $this->assertSame(1, PengumpulanTugas::count());

        $audit = AuditNilai::orderBy('id')->get();
        $this->assertCount(2, $audit);
        $this->assertNull($audit[0]->nilai_lama);
        $this->assertSame(85.0, (float) $audit[1]->nilai_lama);
        $this->assertSame(90.0, (float) $audit[1]->nilai_baru);
    }

    public function test_menyimpan_nilai_yang_sama_tidak_menambah_audit(): void
    {
        $tahun = $this->tahun();
        $semester = $this->semester($tahun);
        $guru = $this->akunGuru('Bu Ratna');
        $penugasan = $this->penugasan($tahun, $guru);
        $tugas = $this->tugas($penugasan, $semester);
        $andi = $this->siswa($penugasan, 'Andi', '1001');
        $url = route('guru.nilai.update', $this->par($penugasan, $tugas));

        $this->actingAs($guru)->put($url, ['nilai' => [$andi->id => '85']]);
        $this->actingAs($guru)->put($url, [
            'nilai' => [$andi->id => '85.00'],
            'catatan' => [$andi->id => 'Catatan baru'],
        ]);

        $this->assertSame(1, AuditNilai::count());
        $this->assertSame('Catatan baru', PengumpulanTugas::firstOrFail()->catatan_guru);
    }

    public function test_kolom_nilai_kosong_tidak_mengubah_nilai_yang_ada(): void
    {
        $tahun = $this->tahun();
        $semester = $this->semester($tahun);
        $guru = $this->akunGuru('Bu Ratna');
        $penugasan = $this->penugasan($tahun, $guru);
        $tugas = $this->tugas($penugasan, $semester);
        $andi = $this->siswa($penugasan, 'Andi', '1001');
        $url = route('guru.nilai.update', $this->par($penugasan, $tugas));

        $this->actingAs($guru)->put($url, ['nilai' => [$andi->id => '85']]);
        $this->actingAs($guru)
            ->put($url, ['nilai' => [$andi->id => '']])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success', 'Tidak ada nilai yang diisi.');

        $this->assertSame(85.0, (float) Nilai::firstOrFail()->nilai);
        $this->assertSame(1, AuditNilai::count());
    }

    public function test_nilai_di_luar_rentang_ditolak_dan_tidak_ada_yang_tersimpan(): void
    {
        $tahun = $this->tahun();
        $semester = $this->semester($tahun);
        $guru = $this->akunGuru('Bu Ratna');
        $penugasan = $this->penugasan($tahun, $guru);
        $tugas = $this->tugas($penugasan, $semester);
        $andi = $this->siswa($penugasan, 'Andi', '1001');
        $budi = $this->siswa($penugasan, 'Budi', '1002');
        $citra = $this->siswa($penugasan, 'Citra', '1003');

        $this->actingAs($guru)
            ->put(route('guru.nilai.update', $this->par($penugasan, $tugas)), [
                'nilai' => [$andi->id => '101', $budi->id => '50', $citra->id => '-1'],
            ])
            ->assertSessionHasErrors(['nilai.'.$andi->id, 'nilai.'.$citra->id]);

        // Budi valid, tetapi seluruh form dibatalkan.
        $this->assertDatabaseCount('nilai', 0);
        $this->assertDatabaseCount('pengumpulan_tugas', 0);
        $this->assertDatabaseCount('audit_nilai', 0);
    }

    public function test_nilai_bukan_angka_ditolak(): void
    {
        $tahun = $this->tahun();
        $semester = $this->semester($tahun);
        $guru = $this->akunGuru('Bu Ratna');
        $penugasan = $this->penugasan($tahun, $guru);
        $tugas = $this->tugas($penugasan, $semester);
        $andi = $this->siswa($penugasan, 'Andi', '1001');

        $this->actingAs($guru)
            ->put(route('guru.nilai.update', $this->par($penugasan, $tugas)), [
                'nilai' => [$andi->id => 'abc'],
            ])
            ->assertSessionHasErrors('nilai.'.$andi->id);

        $this->assertDatabaseCount('nilai', 0);
    }

    public function test_nilai_dengan_lebih_dari_dua_desimal_ditolak(): void
    {
        $tahun = $this->tahun();
        $semester = $this->semester($tahun);
        $guru = $this->akunGuru('Bu Ratna');
        $penugasan = $this->penugasan($tahun, $guru);
        $tugas = $this->tugas($penugasan, $semester);
        $andi = $this->siswa($penugasan, 'Andi', '1001');

        $this->actingAs($guru)
            ->put(route('guru.nilai.update', $this->par($penugasan, $tugas)), [
                'nilai' => [$andi->id => '85.555'],
            ])
            ->assertSessionHasErrors('nilai.'.$andi->id);

        $this->assertDatabaseCount('nilai', 0);
    }

    public function test_siswa_dari_kelas_lain_tidak_bisa_dinilai(): void
    {
        $tahun = $this->tahun();
        $semester = $this->semester($tahun);
        $guru = $this->akunGuru('Bu Ratna');
        $penugasanA = $this->penugasan($tahun, $guru, 'X-A', 'MTK');
        $penugasanB = $this->penugasan($tahun, $guru, 'X-B', 'BIN');
        $tugas = $this->tugas($penugasanA, $semester);
        $andi = $this->siswa($penugasanA, 'Andi', '1001');
        $dewi = $this->siswa($penugasanB, 'Dewi', '2001');

        $this->actingAs($guru)
            ->put(route('guru.nilai.update', $this->par($penugasanA, $tugas)), [
                'nilai' => [$andi->id => '80', $dewi->id => '90'],
            ])
            ->assertSessionHasErrors('nilai');

        $this->assertDatabaseCount('nilai', 0);
        $this->assertDatabaseCount('pengumpulan_tugas', 0);
    }

    public function test_tugas_semester_lain_tidak_bisa_dinilai(): void
    {
        $tahun = $this->tahun();
        $this->semester($tahun);
        $lama = $this->semester($tahun, 'Genap', false);
        $guru = $this->akunGuru('Bu Ratna');
        $penugasan = $this->penugasan($tahun, $guru);
        $tugas = $this->tugas($penugasan, $lama);
        $andi = $this->siswa($penugasan, 'Andi', '1001');

        $this->actingAs($guru)
            ->get(route('guru.nilai.index', $this->par($penugasan, $tugas)))
            ->assertRedirect(route('guru.tugas.index', ['penugasan' => $penugasan]));

        $this->actingAs($guru)
            ->put(route('guru.nilai.update', $this->par($penugasan, $tugas)), [
                'nilai' => [$andi->id => '80'],
            ])
            ->assertSessionHas('error');

        $this->assertDatabaseCount('nilai', 0);
    }

    public function test_guru_lain_tidak_bisa_menilai_tugas_kelas_orang(): void
    {
        $tahun = $this->tahun();
        $semester = $this->semester($tahun);
        $ratna = $this->akunGuru('Bu Ratna');
        $agus = $this->akunGuru('Pak Agus');
        $penugasan = $this->penugasan($tahun, $ratna);
        $tugas = $this->tugas($penugasan, $semester);
        $andi = $this->siswa($penugasan, 'Andi', '1001');

        $this->actingAs($agus);

        $this->get(route('guru.nilai.index', $this->par($penugasan, $tugas)))->assertForbidden();
        $this->put(route('guru.nilai.update', $this->par($penugasan, $tugas)), [
            'nilai' => [$andi->id => '100'],
        ])->assertForbidden();
        $this->get(route('guru.nilai.berkas', $this->par($penugasan, $tugas) + ['siswa' => $andi]))
            ->assertForbidden();

        $this->assertDatabaseCount('nilai', 0);
    }

    public function test_tugas_kelas_lain_tidak_bisa_dinilai_lewat_penugasan_sendiri(): void
    {
        $tahun = $this->tahun();
        $semester = $this->semester($tahun);
        $guru = $this->akunGuru('Bu Ratna');
        $penugasanA = $this->penugasan($tahun, $guru, 'X-A', 'MTK');
        $penugasanB = $this->penugasan($tahun, $guru, 'X-B', 'BIN');
        $tugasB = $this->tugas($penugasanB, $semester);
        $andi = $this->siswa($penugasanA, 'Andi', '1001');

        $this->actingAs($guru);

        $this->get(route('guru.nilai.index', $this->par($penugasanA, $tugasB)))->assertNotFound();
        $this->put(route('guru.nilai.update', $this->par($penugasanA, $tugasB)), [
            'nilai' => [$andi->id => '80'],
        ])->assertNotFound();

        $this->assertDatabaseCount('nilai', 0);
    }

    public function test_guru_bisa_mengunduh_berkas_pengumpulan_siswa(): void
    {
        $tahun = $this->tahun();
        $semester = $this->semester($tahun);
        $guru = $this->akunGuru('Bu Ratna');
        $penugasan = $this->penugasan($tahun, $guru);
        $tugas = $this->tugas($penugasan, $semester);
        $andi = $this->siswa($penugasan, 'Andi', '1001');

        Storage::disk('local')->put('pengumpulan/andi.pdf', 'isi jawaban');
        PengumpulanTugas::create([
            'tugas_id' => $tugas->id,
            'siswa_id' => $andi->id,
            'file_path' => 'pengumpulan/andi.pdf',
            'submitted_at' => now(),
        ]);

        $this->actingAs($guru)
            ->get(route('guru.nilai.berkas', $this->par($penugasan, $tugas) + ['siswa' => $andi]))
            ->assertOk()
            ->assertDownload('andi-tugas-1.pdf');
    }

    public function test_berkas_tanpa_pengumpulan_atau_tanpa_file_menghasilkan_404(): void
    {
        $tahun = $this->tahun();
        $semester = $this->semester($tahun);
        $guru = $this->akunGuru('Bu Ratna');
        $penugasan = $this->penugasan($tahun, $guru);
        $tugas = $this->tugas($penugasan, $semester);
        $andi = $this->siswa($penugasan, 'Andi', '1001');
        $budi = $this->siswa($penugasan, 'Budi', '1002');

        // Budi hanya menjawab dengan teks, Andi belum mengumpulkan sama sekali.
        PengumpulanTugas::create([
            'tugas_id' => $tugas->id,
            'siswa_id' => $budi->id,
            'jawaban_teks' => 'Jawaban saya',
            'submitted_at' => now(),
        ]);

        $this->actingAs($guru);

        $this->get(route('guru.nilai.berkas', $this->par($penugasan, $tugas) + ['siswa' => $andi]))
            ->assertNotFound();
        $this->get(route('guru.nilai.berkas', $this->par($penugasan, $tugas) + ['siswa' => $budi]))
            ->assertNotFound();
    }

    public function test_hitungan_terkumpul_hanya_menghitung_siswa_yang_mengumpulkan(): void
    {
        $tahun = $this->tahun();
        $semester = $this->semester($tahun);
        $guru = $this->akunGuru('Bu Ratna');
        $penugasan = $this->penugasan($tahun, $guru);
        $tugas = $this->tugas($penugasan, $semester);
        $andi = $this->siswa($penugasan, 'Andi', '1001');
        $budi = $this->siswa($penugasan, 'Budi', '1002');

        // Budi benar-benar mengumpulkan, Andi hanya diberi nilai oleh guru.
        PengumpulanTugas::create([
            'tugas_id' => $tugas->id,
            'siswa_id' => $budi->id,
            'submitted_at' => now(),
        ]);

        $this->actingAs($guru)
            ->put(route('guru.nilai.update', $this->par($penugasan, $tugas)), [
                'nilai' => [$andi->id => '75'],
            ])
            ->assertSessionHasNoErrors();

        $this->actingAs($guru)
            ->get(route('guru.tugas.index', ['penugasan' => $penugasan]))
            ->assertOk()
            ->assertSee('1 / 2');
    }
}