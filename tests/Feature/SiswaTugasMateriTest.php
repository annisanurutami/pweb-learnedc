<?php

namespace Tests\Feature;

use App\Models\Guru;
use App\Models\GuruMapel;
use App\Models\Kelas;
use App\Models\KelasSiswa;
use App\Models\MataPelajaran;
use App\Models\Materi;
use App\Models\PengumpulanTugas;
use App\Models\Semester;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use App\Models\Tugas;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SiswaTugasMateriTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->seed(RoleSeeder::class);
        Storage::fake('local');
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

    private function penugasan(TahunAjaran $tahun, string $namaKelas = 'X-A', string $kode = 'MTK', string $namaMapel = 'Matematika'): GuruMapel
    {
        $kelas = Kelas::create([
            'tahun_ajaran_id' => $tahun->id,
            'nama_kelas' => $namaKelas,
            'iuran_kas' => 0,
        ]);
        $mapel = MataPelajaran::firstOrCreate(['kode' => $kode], ['nama' => $namaMapel]);

        $akunGuru = User::factory()->create();
        $akunGuru->assignRole('guru');
        $guru = Guru::create(['user_id' => $akunGuru->id, 'nama' => 'Bu Ratna']);

        return GuruMapel::create([
            'kelas_id' => $kelas->id,
            'mapel_id' => $mapel->id,
            'guru_id' => $guru->id,
        ]);
    }

    /** @param array<int, string> $roles */
    private function akunSiswa(GuruMapel $penugasan, string $nama = 'Andi', string $nis = '1001', array $roles = ['siswa']): User
    {
        $akun = User::factory()->create(['name' => $nama]);
        $akun->assignRole($roles);

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

        return $akun;
    }

    /** @param array<string, mixed> $atribut */
    private function tugas(GuruMapel $penugasan, Semester $semester, array $atribut = []): Tugas
    {
        return Tugas::create($atribut + [
            'guru_mapel_id' => $penugasan->id,
            'semester_id' => $semester->id,
            'judul' => 'Tugas 1',
            'deskripsi' => 'Kerjakan soal 1 sampai 5',
            'file_path' => null,
            'deadline' => now()->addDays(3),
        ]);
    }

    /** @param array<string, mixed> $atribut */
    private function materi(GuruMapel $penugasan, Semester $semester, array $atribut = []): Materi
    {
        return Materi::create($atribut + [
            'guru_mapel_id' => $penugasan->id,
            'semester_id' => $semester->id,
            'judul' => 'Bab 1',
            'deskripsi' => null,
            'file_path' => null,
            'link_url' => 'https://youtu.be/abc',
        ]);
    }

    public function test_guru_tidak_boleh_membuka_halaman_tugas_siswa(): void
    {
        $guru = User::factory()->create();
        $guru->assignRole('guru');

        $this->actingAs($guru)->get(route('siswa.tugas.index'))->assertForbidden();
        $this->actingAs($guru)->get(route('siswa.materi.index'))->assertForbidden();
    }

    public function test_akun_siswa_tanpa_data_siswa_ditolak(): void
    {
        $akun = User::factory()->create();
        $akun->assignRole('siswa'); // tidak ada baris di tabel siswa

        $this->actingAs($akun)->get(route('siswa.tugas.index'))->assertForbidden();
    }

    public function test_siswa_tanpa_kelas_di_tahun_aktif_melihat_pemberitahuan(): void
    {
        $tahun = $this->tahun();
        $this->semester($tahun);
        $akun = User::factory()->create();
        $akun->assignRole('siswa');
        Siswa::create(['user_id' => $akun->id, 'nis' => '9', 'nama' => 'Tanpa Kelas', 'jenis_kelamin' => 'L']);

        $this->actingAs($akun)
            ->get(route('siswa.tugas.index'))
            ->assertOk()
            ->assertSee('belum terdaftar di kelas');
    }

    public function test_siswa_hanya_melihat_tugas_kelasnya(): void
    {
        $tahun = $this->tahun();
        $semester = $this->semester($tahun);
        $penugasanA = $this->penugasan($tahun, 'X-A', 'MTK', 'Matematika');
        $penugasanB = $this->penugasan($tahun, 'X-B', 'BIN', 'Bahasa Indonesia');
        $akun = $this->akunSiswa($penugasanA);
        $this->tugas($penugasanA, $semester, ['judul' => 'Latihan Aljabar']);
        $this->tugas($penugasanB, $semester, ['judul' => 'Menulis Puisi']);

        $this->actingAs($akun)
            ->get(route('siswa.tugas.index'))
            ->assertOk()
            ->assertSee('Latihan Aljabar')
            ->assertSee('Matematika')
            ->assertDontSee('Menulis Puisi');
    }

    public function test_tugas_semester_lain_tidak_ditampilkan(): void
    {
        $tahun = $this->tahun();
        $aktif = $this->semester($tahun);
        $lama = $this->semester($tahun, 'Genap', false);
        $penugasan = $this->penugasan($tahun);
        $akun = $this->akunSiswa($penugasan);
        $this->tugas($penugasan, $aktif, ['judul' => 'Tugas Sekarang']);
        $this->tugas($penugasan, $lama, ['judul' => 'Tugas Lama']);

        $this->actingAs($akun)
            ->get(route('siswa.tugas.index'))
            ->assertSee('Tugas Sekarang')
            ->assertDontSee('Tugas Lama');
    }

    public function test_status_tugas_tampil_sesuai_kondisi(): void
    {
        $tahun = $this->tahun();
        $semester = $this->semester($tahun);
        $penugasan = $this->penugasan($tahun);
        $akun = $this->akunSiswa($penugasan);
        $siswa = $akun->siswa;

        $this->tugas($penugasan, $semester, ['judul' => 'T Belum']);
        $this->tugas($penugasan, $semester, ['judul' => 'T Terlewat', 'deadline' => now()->subDay()]);

        $dikumpul = $this->tugas($penugasan, $semester, ['judul' => 'T Dikumpul']);
        PengumpulanTugas::create(['tugas_id' => $dikumpul->id, 'siswa_id' => $siswa->id, 'submitted_at' => now()]);

        $dinilai = $this->tugas($penugasan, $semester, ['judul' => 'T Dinilai']);
        PengumpulanTugas::create([
            'tugas_id' => $dinilai->id,
            'siswa_id' => $siswa->id,
            'submitted_at' => now(),
            'nilai' => 90,
        ]);

        $this->actingAs($akun)
            ->get(route('siswa.tugas.index'))
            ->assertOk()
            ->assertSee('Belum dikumpulkan')
            ->assertSee('Terlewat')
            ->assertSee('Sudah dikumpulkan')
            ->assertSee('Sudah dinilai');
    }

    public function test_detail_tugas_menampilkan_instruksi_dan_form_pengumpulan(): void
    {
        $tahun = $this->tahun();
        $semester = $this->semester($tahun);
        $penugasan = $this->penugasan($tahun);
        $akun = $this->akunSiswa($penugasan);
        $tugas = $this->tugas($penugasan, $semester);

        $this->actingAs($akun)
            ->get(route('siswa.tugas.show', $tugas))
            ->assertOk()
            ->assertSee('Kerjakan soal 1 sampai 5')
            ->assertSee('Kumpulkan jawaban')
            ->assertSee('name="file"', false);
    }

    public function test_tugas_kelas_lain_tidak_bisa_dibuka(): void
    {
        $tahun = $this->tahun();
        $semester = $this->semester($tahun);
        $penugasanA = $this->penugasan($tahun, 'X-A', 'MTK', 'Matematika');
        $penugasanB = $this->penugasan($tahun, 'X-B', 'BIN', 'Bahasa Indonesia');
        $akun = $this->akunSiswa($penugasanA);
        $tugasB = $this->tugas($penugasanB, $semester);

        $this->actingAs($akun);

        $this->get(route('siswa.tugas.show', $tugasB))->assertNotFound();
        $this->put(route('siswa.tugas.kumpul', $tugasB), ['jawaban_teks' => 'curang'])->assertNotFound();
        $this->get(route('siswa.tugas.lampiran', $tugasB))->assertNotFound();

        $this->assertDatabaseCount('pengumpulan_tugas', 0);
    }

    public function test_siswa_mengumpulkan_jawaban_berupa_file(): void
    {
        $tahun = $this->tahun();
        $semester = $this->semester($tahun);
        $penugasan = $this->penugasan($tahun);
        $akun = $this->akunSiswa($penugasan);
        $tugas = $this->tugas($penugasan, $semester);

        $this->actingAs($akun)
            ->put(route('siswa.tugas.kumpul', $tugas), [
                'file' => UploadedFile::fake()->create('jawaban.pdf', 120, 'application/pdf'),
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('siswa.tugas.show', $tugas));

        $baris = PengumpulanTugas::firstOrFail();
        $this->assertSame($tugas->id, (int) $baris->tugas_id);
        $this->assertSame($akun->siswa->id, (int) $baris->siswa_id);
        $this->assertNotNull($baris->submitted_at);
        $this->assertNull($baris->nilai);
        Storage::disk('local')->assertExists($baris->file_path);
    }

    public function test_siswa_mengumpulkan_jawaban_teks_saja(): void
    {
        $tahun = $this->tahun();
        $semester = $this->semester($tahun);
        $penugasan = $this->penugasan($tahun);
        $akun = $this->akunSiswa($penugasan);
        $tugas = $this->tugas($penugasan, $semester);

        $this->actingAs($akun)
            ->put(route('siswa.tugas.kumpul', $tugas), ['jawaban_teks' => 'Jawabannya 42'])
            ->assertSessionHasNoErrors();

        $baris = PengumpulanTugas::firstOrFail();
        $this->assertSame('Jawabannya 42', $baris->jawaban_teks);
        $this->assertNull($baris->file_path);
    }

    public function test_pengumpulan_kosong_ditolak(): void
    {
        $tahun = $this->tahun();
        $semester = $this->semester($tahun);
        $penugasan = $this->penugasan($tahun);
        $akun = $this->akunSiswa($penugasan);
        $tugas = $this->tugas($penugasan, $semester);

        $this->actingAs($akun)
            ->put(route('siswa.tugas.kumpul', $tugas), [])
            ->assertSessionHasErrors('file');

        $this->assertDatabaseCount('pengumpulan_tugas', 0);
    }

    public function test_tipe_dan_ukuran_file_dibatasi(): void
    {
        $tahun = $this->tahun();
        $semester = $this->semester($tahun);
        $penugasan = $this->penugasan($tahun);
        $akun = $this->akunSiswa($penugasan);
        $tugas = $this->tugas($penugasan, $semester);
        $url = route('siswa.tugas.kumpul', $tugas);

        $this->actingAs($akun)
            ->put($url, ['file' => UploadedFile::fake()->create('virus.exe', 10, 'application/x-msdownload')])
            ->assertSessionHasErrors('file');

        $this->actingAs($akun)
            ->put($url, ['file' => UploadedFile::fake()->create('besar.pdf', 10241, 'application/pdf')])
            ->assertSessionHasErrors('file');

        $this->assertDatabaseCount('pengumpulan_tugas', 0);
    }

    public function test_upload_ulang_mengganti_file_lama_dan_tetap_satu_baris(): void
    {
        $tahun = $this->tahun();
        $semester = $this->semester($tahun);
        $penugasan = $this->penugasan($tahun);
        $akun = $this->akunSiswa($penugasan);
        $tugas = $this->tugas($penugasan, $semester);
        $url = route('siswa.tugas.kumpul', $tugas);

        $this->actingAs($akun)->put($url, ['file' => UploadedFile::fake()->create('v1.pdf', 50, 'application/pdf')]);
        $lama = PengumpulanTugas::firstOrFail()->file_path;

        $this->actingAs($akun)
            ->put($url, ['file' => UploadedFile::fake()->create('v2.pdf', 60, 'application/pdf')])
            ->assertSessionHas('success', 'Jawaban diperbarui.');

        $this->assertSame(1, PengumpulanTugas::count());
        $baru = PengumpulanTugas::firstOrFail()->file_path;
        $this->assertNotSame($lama, $baru);
        Storage::disk('local')->assertMissing($lama);
        Storage::disk('local')->assertExists($baru);
    }

    public function test_mengumpulkan_teks_ulang_tanpa_file_baru_mempertahankan_file_lama(): void
    {
        $tahun = $this->tahun();
        $semester = $this->semester($tahun);
        $penugasan = $this->penugasan($tahun);
        $akun = $this->akunSiswa($penugasan);
        $tugas = $this->tugas($penugasan, $semester);
        $url = route('siswa.tugas.kumpul', $tugas);

        $this->actingAs($akun)->put($url, ['file' => UploadedFile::fake()->create('v1.pdf', 50, 'application/pdf')]);
        $path = PengumpulanTugas::firstOrFail()->file_path;

        $this->actingAs($akun)->put($url, ['jawaban_teks' => 'Catatan tambahan'])->assertSessionHasNoErrors();

        $baris = PengumpulanTugas::firstOrFail();
        $this->assertSame($path, $baris->file_path);
        $this->assertSame('Catatan tambahan', $baris->jawaban_teks);
        Storage::disk('local')->assertExists($path);
    }

    public function test_setelah_deadline_jawaban_tidak_bisa_dikumpulkan(): void
    {
        $tahun = $this->tahun();
        $semester = $this->semester($tahun);
        $penugasan = $this->penugasan($tahun);
        $akun = $this->akunSiswa($penugasan);
        $tugas = $this->tugas($penugasan, $semester, ['deadline' => now()->subMinute()]);

        $this->actingAs($akun)
            ->put(route('siswa.tugas.kumpul', $tugas), ['jawaban_teks' => 'telat'])
            ->assertSessionHas('error');

        $this->assertDatabaseCount('pengumpulan_tugas', 0);

        $this->actingAs($akun)
            ->get(route('siswa.tugas.show', $tugas))
            ->assertSee('Deadline sudah lewat')
            ->assertDontSee('Kumpulkan jawaban');
    }

    public function test_setelah_dinilai_jawaban_tidak_bisa_diubah_dan_nilai_terlihat(): void
    {
        $tahun = $this->tahun();
        $semester = $this->semester($tahun);
        $penugasan = $this->penugasan($tahun);
        $akun = $this->akunSiswa($penugasan);
        $tugas = $this->tugas($penugasan, $semester);
        PengumpulanTugas::create([
            'tugas_id' => $tugas->id,
            'siswa_id' => $akun->siswa->id,
            'jawaban_teks' => 'Jawaban awal',
            'submitted_at' => now(),
            'nilai' => 87.5,
            'catatan_guru' => 'Bagus sekali',
        ]);

        $this->actingAs($akun)
            ->put(route('siswa.tugas.kumpul', $tugas), ['jawaban_teks' => 'Diubah diam-diam'])
            ->assertSessionHas('error');

        $this->assertSame('Jawaban awal', PengumpulanTugas::firstOrFail()->jawaban_teks);

        $this->actingAs($akun)
            ->get(route('siswa.tugas.show', $tugas))
            ->assertOk()
            ->assertSee('87.5')
            ->assertSee('Bagus sekali')
            ->assertDontSee('Kumpulkan ulang jawaban');
    }

    public function test_siswa_bisa_mengunduh_lampiran_soal_dan_jawabannya_sendiri(): void
    {
        $tahun = $this->tahun();
        $semester = $this->semester($tahun);
        $penugasan = $this->penugasan($tahun);
        $akun = $this->akunSiswa($penugasan);

        Storage::disk('local')->put('tugas/soal.pdf', 'isi soal');
        Storage::disk('local')->put('pengumpulan/jawab.pdf', 'isi jawaban');
        $tugas = $this->tugas($penugasan, $semester, ['file_path' => 'tugas/soal.pdf']);
        PengumpulanTugas::create([
            'tugas_id' => $tugas->id,
            'siswa_id' => $akun->siswa->id,
            'file_path' => 'pengumpulan/jawab.pdf',
            'submitted_at' => now(),
        ]);

        $this->actingAs($akun)
            ->get(route('siswa.tugas.lampiran', $tugas))
            ->assertOk()
            ->assertDownload('tugas-1.pdf');

        $this->actingAs($akun)
            ->get(route('siswa.tugas.jawaban', $tugas))
            ->assertOk()
            ->assertDownload('tugas-1-jawaban.pdf');
    }

    public function test_siswa_tidak_bisa_mengunduh_jawaban_siswa_lain(): void
    {
        $tahun = $this->tahun();
        $semester = $this->semester($tahun);
        $penugasan = $this->penugasan($tahun);
        $andi = $this->akunSiswa($penugasan, 'Andi', '1001');
        $budi = $this->akunSiswa($penugasan, 'Budi', '1002');
        $tugas = $this->tugas($penugasan, $semester);

        Storage::disk('local')->put('pengumpulan/andi.pdf', 'rahasia Andi');
        PengumpulanTugas::create([
            'tugas_id' => $tugas->id,
            'siswa_id' => $andi->siswa->id,
            'file_path' => 'pengumpulan/andi.pdf',
            'submitted_at' => now(),
        ]);

        // Budi tidak punya jawaban, jadi tidak ada berkas yang bisa diunduh.
        $this->actingAs($budi)
            ->get(route('siswa.tugas.jawaban', $tugas))
            ->assertNotFound();
    }

    public function test_siswa_melihat_materi_kelasnya_per_mata_pelajaran(): void
    {
        $tahun = $this->tahun();
        $semester = $this->semester($tahun);
        $penugasanA = $this->penugasan($tahun, 'X-A', 'MTK', 'Matematika');
        $penugasanB = $this->penugasan($tahun, 'X-B', 'BIN', 'Bahasa Indonesia');
        $akun = $this->akunSiswa($penugasanA);
        $this->materi($penugasanA, $semester, ['judul' => 'Persamaan Linear']);
        $this->materi($penugasanB, $semester, ['judul' => 'Teks Narasi']);

        $this->actingAs($akun)
            ->get(route('siswa.materi.index'))
            ->assertOk()
            ->assertSee('Matematika')
            ->assertSee('Persamaan Linear')
            ->assertDontSee('Teks Narasi')
            ->assertDontSee('Bahasa Indonesia');
    }

    public function test_siswa_bisa_mengunduh_materi_kelasnya(): void
    {
        $tahun = $this->tahun();
        $semester = $this->semester($tahun);
        $penugasan = $this->penugasan($tahun);
        $akun = $this->akunSiswa($penugasan);

        Storage::disk('local')->put('materi/bab1.pdf', 'isi materi');
        $materi = $this->materi($penugasan, $semester, ['file_path' => 'materi/bab1.pdf', 'link_url' => null]);

        $this->actingAs($akun)
            ->get(route('siswa.materi.unduh', $materi))
            ->assertOk()
            ->assertDownload('bab-1.pdf');
    }

    public function test_materi_kelas_lain_atau_tanpa_file_tidak_bisa_diunduh(): void
    {
        $tahun = $this->tahun();
        $semester = $this->semester($tahun);
        $penugasanA = $this->penugasan($tahun, 'X-A', 'MTK', 'Matematika');
        $penugasanB = $this->penugasan($tahun, 'X-B', 'BIN', 'Bahasa Indonesia');
        $akun = $this->akunSiswa($penugasanA);

        Storage::disk('local')->put('materi/b.pdf', 'rahasia kelas B');
        $materiB = $this->materi($penugasanB, $semester, ['file_path' => 'materi/b.pdf']);
        $materiLinkSaja = $this->materi($penugasanA, $semester);

        $this->actingAs($akun)->get(route('siswa.materi.unduh', $materiB))->assertNotFound();
        $this->actingAs($akun)->get(route('siswa.materi.unduh', $materiLinkSaja))->assertNotFound();
    }

    public function test_bendahara_yang_juga_siswa_tetap_bisa_membuka_tugas(): void
    {
        $tahun = $this->tahun();
        $semester = $this->semester($tahun);
        $penugasan = $this->penugasan($tahun);
        $akun = $this->akunSiswa($penugasan, 'Bendahara Demo', '1005', ['siswa', 'bendahara']);
        $this->tugas($penugasan, $semester, ['judul' => 'Tugas Bendahara']);

        $this->actingAs($akun)
            ->get(route('siswa.tugas.index'))
            ->assertOk()
            ->assertSee('Tugas Bendahara');
    }

    public function test_sidebar_siswa_memuat_menu_materi_dan_tugas(): void
    {
        $tahun = $this->tahun();
        $this->semester($tahun);
        $penugasan = $this->penugasan($tahun);
        $akun = $this->akunSiswa($penugasan);

        $this->actingAs($akun)
            ->get(route('siswa.dashboard'))
            ->assertOk()
            ->assertSee(route('siswa.materi.index'), false)
            ->assertSee(route('siswa.tugas.index'), false);
    }
}