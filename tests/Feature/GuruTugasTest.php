<?php

namespace Tests\Feature;

use App\Models\Guru;
use App\Models\GuruMapel;
use App\Models\Kelas;
use App\Models\MataPelajaran;
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

class GuruTugasTest extends TestCase
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
        $mapel = MataPelajaran::create(['kode' => $kode, 'nama' => 'Mapel ' . $kode]);

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

    /** Nilai deadline seperti yang dikirim <input type="datetime-local">. */
    private function deadlineForm(int $hari = 3): string
    {
        return now()->addDays($hari)->format('Y-m-d\TH:i');
    }

    public function test_zona_waktu_aplikasi_adalah_wib(): void
    {
        $this->assertSame('Asia/Jakarta', config('app.timezone'));
    }

    public function test_siswa_tidak_boleh_membuka_tugas(): void
    {
        $penugasan = $this->penugasan($this->tahun(), $this->akunGuru('Bu Ratna'));
        $siswa = User::factory()->create();
        $siswa->assignRole('siswa');

        $this->actingAs($siswa)
            ->get(route('guru.tugas.index', ['penugasan' => $penugasan]))
            ->assertForbidden();
    }

    public function test_guru_melihat_daftar_tugas_dengan_status(): void
    {
        $tahun = $this->tahun();
        $semester = $this->semester($tahun);
        $guru = $this->akunGuru('Bu Ratna');
        $penugasan = $this->penugasan($tahun, $guru);
        $this->tugas($penugasan, $semester, ['judul' => 'Latihan Soal']);
        $this->tugas($penugasan, $semester, ['judul' => 'Tugas Lama', 'deadline' => now()->subDay()]);

        $this->actingAs($guru)
            ->get(route('guru.tugas.index', ['penugasan' => $penugasan]))
            ->assertOk()
            ->assertSee('Latihan Soal')
            ->assertSee('Terbuka')
            ->assertSee('Tugas Lama')
            ->assertSee('Ditutup');
    }

    public function test_tugas_semester_lain_tidak_ditampilkan(): void
    {
        $tahun = $this->tahun();
        $aktif = $this->semester($tahun);
        $lama = $this->semester($tahun, 'Genap', false);
        $guru = $this->akunGuru('Bu Ratna');
        $penugasan = $this->penugasan($tahun, $guru);
        $this->tugas($penugasan, $aktif, ['judul' => 'Tugas Baru']);
        $this->tugas($penugasan, $lama, ['judul' => 'Tugas Semester Lama']);

        $this->actingAs($guru)
            ->get(route('guru.tugas.index', ['penugasan' => $penugasan]))
            ->assertSee('Tugas Baru')
            ->assertDontSee('Tugas Semester Lama');
    }

    public function test_guru_bisa_menambah_tugas_tanpa_lampiran(): void
    {
        $tahun = $this->tahun();
        $semester = $this->semester($tahun);
        $guru = $this->akunGuru('Bu Ratna');
        $penugasan = $this->penugasan($tahun, $guru);
        $deadline = $this->deadlineForm();

        $this->actingAs($guru)
            ->post(route('guru.tugas.store', ['penugasan' => $penugasan]), [
                'judul' => 'Latihan Bab 1',
                'deskripsi' => 'Kerjakan soal 1 sampai 10',
                'deadline' => $deadline,
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('guru.tugas.index', ['penugasan' => $penugasan]));

        $tugas = Tugas::firstOrFail();
        $this->assertSame($penugasan->id, $tugas->guru_mapel_id);
        $this->assertSame($semester->id, $tugas->semester_id);
        $this->assertSame('Latihan Bab 1', $tugas->judul);
        $this->assertNull($tugas->file_path);
        $this->assertSame($deadline, $tugas->deadline->format('Y-m-d\TH:i'));
        $this->assertSame(0, (int) $tugas->deadline->format('s'));
    }

    public function test_guru_bisa_menambah_tugas_dengan_lampiran(): void
    {
        $tahun = $this->tahun();
        $this->semester($tahun);
        $guru = $this->akunGuru('Bu Ratna');
        $penugasan = $this->penugasan($tahun, $guru);

        $this->actingAs($guru)
            ->post(route('guru.tugas.store', ['penugasan' => $penugasan]), [
                'judul' => 'Latihan Bab 1',
                'deadline' => $this->deadlineForm(),
                'lampiran' => UploadedFile::fake()->create('soal.pdf', 150, 'application/pdf'),
            ])
            ->assertSessionHasNoErrors();

        $tugas = Tugas::firstOrFail();
        $this->assertNotNull($tugas->file_path);
        Storage::disk('local')->assertExists($tugas->file_path);
    }

    public function test_deadline_di_masa_lalu_ditolak_saat_membuat_tugas(): void
    {
        $tahun = $this->tahun();
        $this->semester($tahun);
        $guru = $this->akunGuru('Bu Ratna');
        $penugasan = $this->penugasan($tahun, $guru);

        $this->actingAs($guru)
            ->post(route('guru.tugas.store', ['penugasan' => $penugasan]), [
                'judul' => 'Terlambat',
                'deadline' => now()->subDay()->format('Y-m-d\TH:i'),
            ])
            ->assertSessionHasErrors('deadline');

        $this->assertDatabaseCount('tugas', 0);
    }

    public function test_format_deadline_yang_salah_ditolak(): void
    {
        $tahun = $this->tahun();
        $this->semester($tahun);
        $guru = $this->akunGuru('Bu Ratna');
        $penugasan = $this->penugasan($tahun, $guru);

        $this->actingAs($guru)
            ->post(route('guru.tugas.store', ['penugasan' => $penugasan]), [
                'judul' => 'Format salah',
                'deadline' => 'besok sore',
            ])
            ->assertSessionHasErrors('deadline');

        $this->assertDatabaseCount('tugas', 0);
    }

    public function test_deadline_wajib_diisi(): void
    {
        $tahun = $this->tahun();
        $this->semester($tahun);
        $guru = $this->akunGuru('Bu Ratna');
        $penugasan = $this->penugasan($tahun, $guru);

        $this->actingAs($guru)
            ->post(route('guru.tugas.store', ['penugasan' => $penugasan]), ['judul' => 'Tanpa deadline'])
            ->assertSessionHasErrors('deadline');
    }

    public function test_tipe_lampiran_yang_tidak_diizinkan_ditolak(): void
    {
        $tahun = $this->tahun();
        $this->semester($tahun);
        $guru = $this->akunGuru('Bu Ratna');
        $penugasan = $this->penugasan($tahun, $guru);

        $this->actingAs($guru)
            ->post(route('guru.tugas.store', ['penugasan' => $penugasan]), [
                'judul' => 'Berbahaya',
                'deadline' => $this->deadlineForm(),
                'lampiran' => UploadedFile::fake()->create('virus.exe', 10, 'application/x-msdownload'),
            ])
            ->assertSessionHasErrors('lampiran');

        $this->assertDatabaseCount('tugas', 0);
    }

    public function test_tanpa_semester_aktif_tugas_tidak_bisa_ditambah(): void
    {
        $tahun = $this->tahun();
        $guru = $this->akunGuru('Bu Ratna');
        $penugasan = $this->penugasan($tahun, $guru);

        $this->actingAs($guru)
            ->get(route('guru.tugas.create', ['penugasan' => $penugasan]))
            ->assertRedirect(route('guru.tugas.index', ['penugasan' => $penugasan]));

        $this->actingAs($guru)
            ->post(route('guru.tugas.store', ['penugasan' => $penugasan]), [
                'judul' => 'Tugas',
                'deadline' => $this->deadlineForm(),
            ])
            ->assertSessionHas('error');

        $this->assertDatabaseCount('tugas', 0);
    }

    public function test_mengubah_tugas_mempertahankan_lampiran_dan_boleh_memperpanjang_deadline(): void
    {
        $tahun = $this->tahun();
        $semester = $this->semester($tahun);
        $guru = $this->akunGuru('Bu Ratna');
        $penugasan = $this->penugasan($tahun, $guru);

        Storage::disk('local')->put('tugas/lama.pdf', 'isi lama');
        $tugas = $this->tugas($penugasan, $semester, [
            'file_path' => 'tugas/lama.pdf',
            'deadline' => now()->subDay(),
        ]);
        $baru = $this->deadlineForm(5);

        $this->actingAs($guru)
            ->put(route('guru.tugas.update', ['penugasan' => $penugasan, 'tugas' => $tugas]), [
                'judul' => 'Tugas 1 revisi',
                'deadline' => $baru,
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('guru.tugas.index', ['penugasan' => $penugasan]));

        $tugas->refresh();
        $this->assertSame('Tugas 1 revisi', $tugas->judul);
        $this->assertSame($baru, $tugas->deadline->format('Y-m-d\TH:i'));
        $this->assertSame('tugas/lama.pdf', $tugas->file_path);
        Storage::disk('local')->assertExists('tugas/lama.pdf');
    }

    public function test_mengganti_lampiran_menghapus_file_lama(): void
    {
        $tahun = $this->tahun();
        $semester = $this->semester($tahun);
        $guru = $this->akunGuru('Bu Ratna');
        $penugasan = $this->penugasan($tahun, $guru);

        Storage::disk('local')->put('tugas/lama.pdf', 'isi lama');
        $tugas = $this->tugas($penugasan, $semester, ['file_path' => 'tugas/lama.pdf']);

        $this->actingAs($guru)
            ->put(route('guru.tugas.update', ['penugasan' => $penugasan, 'tugas' => $tugas]), [
                'judul' => 'Tugas 1',
                'deadline' => $this->deadlineForm(),
                'lampiran' => UploadedFile::fake()->create('baru.pdf', 100, 'application/pdf'),
            ])
            ->assertSessionHasNoErrors();

        $tugas->refresh();
        $this->assertNotSame('tugas/lama.pdf', $tugas->file_path);
        Storage::disk('local')->assertMissing('tugas/lama.pdf');
        Storage::disk('local')->assertExists($tugas->file_path);
    }

    public function test_centang_hapus_lampiran_menghapus_file(): void
    {
        $tahun = $this->tahun();
        $semester = $this->semester($tahun);
        $guru = $this->akunGuru('Bu Ratna');
        $penugasan = $this->penugasan($tahun, $guru);

        Storage::disk('local')->put('tugas/lama.pdf', 'isi lama');
        $tugas = $this->tugas($penugasan, $semester, ['file_path' => 'tugas/lama.pdf']);

        $this->actingAs($guru)
            ->put(route('guru.tugas.update', ['penugasan' => $penugasan, 'tugas' => $tugas]), [
                'judul' => 'Tugas 1',
                'deadline' => $this->deadlineForm(),
                'hapus_lampiran' => '1',
            ])
            ->assertSessionHasNoErrors();

        $this->assertNull($tugas->fresh()->file_path);
        Storage::disk('local')->assertMissing('tugas/lama.pdf');
    }

    public function test_menghapus_tugas_ikut_menghapus_lampirannya(): void
    {
        $tahun = $this->tahun();
        $semester = $this->semester($tahun);
        $guru = $this->akunGuru('Bu Ratna');
        $penugasan = $this->penugasan($tahun, $guru);

        Storage::disk('local')->put('tugas/hapus.pdf', 'isi');
        $tugas = $this->tugas($penugasan, $semester, ['file_path' => 'tugas/hapus.pdf']);

        $this->actingAs($guru)
            ->delete(route('guru.tugas.destroy', ['penugasan' => $penugasan, 'tugas' => $tugas]))
            ->assertRedirect(route('guru.tugas.index', ['penugasan' => $penugasan]));

        $this->assertDatabaseMissing('tugas', ['id' => $tugas->id]);
        Storage::disk('local')->assertMissing('tugas/hapus.pdf');
    }

    public function test_tugas_yang_sudah_dikumpulkan_siswa_tidak_bisa_dihapus(): void
    {
        $tahun = $this->tahun();
        $semester = $this->semester($tahun);
        $guru = $this->akunGuru('Bu Ratna');
        $penugasan = $this->penugasan($tahun, $guru);
        $tugas = $this->tugas($penugasan, $semester);

        $akunSiswa = User::factory()->create();
        $akunSiswa->assignRole('siswa');
        $siswa = Siswa::create([
            'user_id' => $akunSiswa->id,
            'nis' => '1001',
            'nama' => 'Andi',
            'jenis_kelamin' => 'L',
        ]);
        PengumpulanTugas::create([
            'tugas_id' => $tugas->id,
            'siswa_id' => $siswa->id,
            'submitted_at' => now(),
        ]);

        $this->actingAs($guru)
            ->delete(route('guru.tugas.destroy', ['penugasan' => $penugasan, 'tugas' => $tugas]))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('tugas', ['id' => $tugas->id]);
        $this->assertDatabaseCount('pengumpulan_tugas', 1);
    }

    public function test_guru_bisa_mengunduh_lampiran_tugas(): void
    {
        $tahun = $this->tahun();
        $semester = $this->semester($tahun);
        $guru = $this->akunGuru('Bu Ratna');
        $penugasan = $this->penugasan($tahun, $guru);

        Storage::disk('local')->put('tugas/abc.pdf', 'isi pdf');
        $tugas = $this->tugas($penugasan, $semester, ['file_path' => 'tugas/abc.pdf']);

        $this->actingAs($guru)
            ->get(route('guru.tugas.lampiran', ['penugasan' => $penugasan, 'tugas' => $tugas]))
            ->assertOk()
            ->assertDownload('tugas-1.pdf');
    }

    public function test_guru_lain_tidak_bisa_mengelola_tugas_kelas_orang(): void
    {
        $tahun = $this->tahun();
        $semester = $this->semester($tahun);
        $ratna = $this->akunGuru('Bu Ratna');
        $agus = $this->akunGuru('Pak Agus');
        $penugasan = $this->penugasan($tahun, $ratna);

        Storage::disk('local')->put('tugas/abc.pdf', 'isi');
        $tugas = $this->tugas($penugasan, $semester, ['file_path' => 'tugas/abc.pdf']);
        $par = ['penugasan' => $penugasan, 'tugas' => $tugas];

        $this->actingAs($agus);

        $this->get(route('guru.tugas.index', ['penugasan' => $penugasan]))->assertForbidden();
        $this->get(route('guru.tugas.create', ['penugasan' => $penugasan]))->assertForbidden();
        $this->post(route('guru.tugas.store', ['penugasan' => $penugasan]), [
            'judul' => 'Selundupan',
            'deadline' => $this->deadlineForm(),
        ])->assertForbidden();
        $this->get(route('guru.tugas.edit', $par))->assertForbidden();
        $this->put(route('guru.tugas.update', $par), [
            'judul' => 'Diubah',
            'deadline' => $this->deadlineForm(),
        ])->assertForbidden();
        $this->delete(route('guru.tugas.destroy', $par))->assertForbidden();
        $this->get(route('guru.tugas.lampiran', $par))->assertForbidden();

        $this->assertDatabaseCount('tugas', 1);
        $this->assertSame('Tugas 1', $tugas->fresh()->judul);
    }

    public function test_tugas_kelas_lain_tidak_bisa_diakses_lewat_penugasan_sendiri(): void
    {
        $tahun = $this->tahun();
        $semester = $this->semester($tahun);
        $guru = $this->akunGuru('Bu Ratna');
        $penugasanA = $this->penugasan($tahun, $guru, 'X-A', 'MTK');
        $penugasanB = $this->penugasan($tahun, $guru, 'X-B', 'BIN');
        $tugasB = $this->tugas($penugasanB, $semester);
        $par = ['penugasan' => $penugasanA, 'tugas' => $tugasB];

        $this->actingAs($guru);

        $this->get(route('guru.tugas.edit', $par))->assertNotFound();
        $this->put(route('guru.tugas.update', $par), [
            'judul' => 'Diubah',
            'deadline' => $this->deadlineForm(),
        ])->assertNotFound();
        $this->delete(route('guru.tugas.destroy', $par))->assertNotFound();
        $this->get(route('guru.tugas.lampiran', $par))->assertNotFound();

        $this->assertSame('Tugas 1', $tugasB->fresh()->judul);
    }
}