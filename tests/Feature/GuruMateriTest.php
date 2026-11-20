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
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class GuruMateriTest extends TestCase
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

    public function test_siswa_tidak_boleh_membuka_materi(): void
    {
        $tahun = $this->tahun();
        $penugasan = $this->penugasan($tahun, $this->akunGuru('Bu Ratna'));
        $siswa = User::factory()->create();
        $siswa->assignRole('siswa');

        $this->actingAs($siswa)
            ->get(route('guru.materi.index', ['penugasan' => $penugasan]))
            ->assertForbidden();
    }

    public function test_guru_melihat_daftar_materi_kelasnya(): void
    {
        $tahun = $this->tahun();
        $semester = $this->semester($tahun);
        $guru = $this->akunGuru('Bu Ratna');
        $penugasan = $this->penugasan($tahun, $guru);
        $this->materi($penugasan, $semester, ['judul' => 'Persamaan Linear']);

        $this->actingAs($guru)
            ->get(route('guru.materi.index', ['penugasan' => $penugasan]))
            ->assertOk()
            ->assertSee('Persamaan Linear');
    }

    public function test_materi_semester_lain_tidak_ditampilkan(): void
    {
        $tahun = $this->tahun();
        $aktif = $this->semester($tahun);
        $lama = $this->semester($tahun, 'Genap', false);
        $guru = $this->akunGuru('Bu Ratna');
        $penugasan = $this->penugasan($tahun, $guru);
        $this->materi($penugasan, $aktif, ['judul' => 'Materi Baru']);
        $this->materi($penugasan, $lama, ['judul' => 'Materi Lama']);

        $this->actingAs($guru)
            ->get(route('guru.materi.index', ['penugasan' => $penugasan]))
            ->assertSee('Materi Baru')
            ->assertDontSee('Materi Lama');
    }

    public function test_guru_bisa_menambah_materi_dengan_file_pdf(): void
    {
        $tahun = $this->tahun();
        $semester = $this->semester($tahun);
        $guru = $this->akunGuru('Bu Ratna');
        $penugasan = $this->penugasan($tahun, $guru);

        $this->actingAs($guru)
            ->post(route('guru.materi.store', ['penugasan' => $penugasan]), [
                'judul' => 'Bab 1',
                'deskripsi' => 'Pengantar',
                'file' => UploadedFile::fake()->create('bab1.pdf', 200, 'application/pdf'),
            ])
            ->assertRedirect(route('guru.materi.index', ['penugasan' => $penugasan]));

        $this->assertDatabaseHas('materi', [
            'guru_mapel_id' => $penugasan->id,
            'semester_id' => $semester->id,
            'judul' => 'Bab 1',
        ]);

        $materi = Materi::firstOrFail();
        $this->assertNotNull($materi->file_path);
        Storage::disk('local')->assertExists($materi->file_path);
    }

    public function test_guru_bisa_menambah_materi_berupa_link_video(): void
    {
        $tahun = $this->tahun();
        $this->semester($tahun);
        $guru = $this->akunGuru('Bu Ratna');
        $penugasan = $this->penugasan($tahun, $guru);

        $this->actingAs($guru)
            ->post(route('guru.materi.store', ['penugasan' => $penugasan]), [
                'judul' => 'Video Bab 1',
                'link_url' => 'https://youtu.be/abc123',
            ])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('materi', [
            'judul' => 'Video Bab 1',
            'link_url' => 'https://youtu.be/abc123',
            'file_path' => null,
        ]);
    }

    public function test_materi_tanpa_file_dan_tanpa_link_ditolak(): void
    {
        $tahun = $this->tahun();
        $this->semester($tahun);
        $guru = $this->akunGuru('Bu Ratna');
        $penugasan = $this->penugasan($tahun, $guru);

        $this->actingAs($guru)
            ->post(route('guru.materi.store', ['penugasan' => $penugasan]), ['judul' => 'Kosong'])
            ->assertSessionHasErrors('file');

        $this->assertDatabaseCount('materi', 0);
    }

    public function test_tipe_file_yang_tidak_diizinkan_ditolak(): void
    {
        $tahun = $this->tahun();
        $this->semester($tahun);
        $guru = $this->akunGuru('Bu Ratna');
        $penugasan = $this->penugasan($tahun, $guru);

        $this->actingAs($guru)
            ->post(route('guru.materi.store', ['penugasan' => $penugasan]), [
                'judul' => 'Berbahaya',
                'file' => UploadedFile::fake()->create('virus.exe', 10, 'application/x-msdownload'),
            ])
            ->assertSessionHasErrors('file');

        $this->assertDatabaseCount('materi', 0);
    }

    public function test_file_lebih_dari_10_mb_ditolak(): void
    {
        $tahun = $this->tahun();
        $this->semester($tahun);
        $guru = $this->akunGuru('Bu Ratna');
        $penugasan = $this->penugasan($tahun, $guru);

        $this->actingAs($guru)
            ->post(route('guru.materi.store', ['penugasan' => $penugasan]), [
                'judul' => 'Terlalu besar',
                'file' => UploadedFile::fake()->create('besar.pdf', 10241, 'application/pdf'),
            ])
            ->assertSessionHasErrors('file');

        $this->assertDatabaseCount('materi', 0);
    }

    public function test_link_bukan_http_ditolak(): void
    {
        $tahun = $this->tahun();
        $this->semester($tahun);
        $guru = $this->akunGuru('Bu Ratna');
        $penugasan = $this->penugasan($tahun, $guru);

        $this->actingAs($guru)
            ->post(route('guru.materi.store', ['penugasan' => $penugasan]), [
                'judul' => 'Link jahat',
                'link_url' => 'javascript:alert(1)',
            ])
            ->assertSessionHasErrors('link_url');

        $this->assertDatabaseCount('materi', 0);
    }

    public function test_tanpa_semester_aktif_materi_tidak_bisa_ditambah(): void
    {
        $tahun = $this->tahun();
        $guru = $this->akunGuru('Bu Ratna');
        $penugasan = $this->penugasan($tahun, $guru);

        $this->actingAs($guru)
            ->get(route('guru.materi.create', ['penugasan' => $penugasan]))
            ->assertRedirect(route('guru.materi.index', ['penugasan' => $penugasan]));

        $this->actingAs($guru)
            ->post(route('guru.materi.store', ['penugasan' => $penugasan]), [
                'judul' => 'Bab 1',
                'link_url' => 'https://youtu.be/abc',
            ])
            ->assertSessionHas('error');

        $this->assertDatabaseCount('materi', 0);
    }

    public function test_mengubah_materi_tanpa_file_baru_mempertahankan_file_lama(): void
    {
        $tahun = $this->tahun();
        $semester = $this->semester($tahun);
        $guru = $this->akunGuru('Bu Ratna');
        $penugasan = $this->penugasan($tahun, $guru);

        Storage::disk('local')->put('materi/lama.pdf', 'isi lama');
        $materi = $this->materi($penugasan, $semester, ['file_path' => 'materi/lama.pdf', 'link_url' => null]);

        $this->actingAs($guru)
            ->put(route('guru.materi.update', ['penugasan' => $penugasan, 'materi' => $materi]), [
                'judul' => 'Bab 1 revisi',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('guru.materi.index', ['penugasan' => $penugasan]));

        $materi->refresh();
        $this->assertSame('Bab 1 revisi', $materi->judul);
        $this->assertSame('materi/lama.pdf', $materi->file_path);
        Storage::disk('local')->assertExists('materi/lama.pdf');
    }

    public function test_mengganti_file_menghapus_file_lama(): void
    {
        $tahun = $this->tahun();
        $semester = $this->semester($tahun);
        $guru = $this->akunGuru('Bu Ratna');
        $penugasan = $this->penugasan($tahun, $guru);

        Storage::disk('local')->put('materi/lama.pdf', 'isi lama');
        $materi = $this->materi($penugasan, $semester, ['file_path' => 'materi/lama.pdf', 'link_url' => null]);

        $this->actingAs($guru)
            ->put(route('guru.materi.update', ['penugasan' => $penugasan, 'materi' => $materi]), [
                'judul' => 'Bab 1',
                'file' => UploadedFile::fake()->create('baru.pdf', 100, 'application/pdf'),
            ])
            ->assertSessionHasNoErrors();

        $materi->refresh();
        $this->assertNotSame('materi/lama.pdf', $materi->file_path);
        Storage::disk('local')->assertMissing('materi/lama.pdf');
        Storage::disk('local')->assertExists($materi->file_path);
    }

    public function test_menghapus_materi_ikut_menghapus_filenya(): void
    {
        $tahun = $this->tahun();
        $semester = $this->semester($tahun);
        $guru = $this->akunGuru('Bu Ratna');
        $penugasan = $this->penugasan($tahun, $guru);

        Storage::disk('local')->put('materi/hapus.pdf', 'isi');
        $materi = $this->materi($penugasan, $semester, ['file_path' => 'materi/hapus.pdf', 'link_url' => null]);

        $this->actingAs($guru)
            ->delete(route('guru.materi.destroy', ['penugasan' => $penugasan, 'materi' => $materi]))
            ->assertRedirect(route('guru.materi.index', ['penugasan' => $penugasan]));

        $this->assertDatabaseMissing('materi', ['id' => $materi->id]);
        Storage::disk('local')->assertMissing('materi/hapus.pdf');
    }

    public function test_guru_bisa_mengunduh_file_materi(): void
    {
        $tahun = $this->tahun();
        $semester = $this->semester($tahun);
        $guru = $this->akunGuru('Bu Ratna');
        $penugasan = $this->penugasan($tahun, $guru);

        Storage::disk('local')->put('materi/abc.pdf', 'isi pdf');
        $materi = $this->materi($penugasan, $semester, ['file_path' => 'materi/abc.pdf', 'link_url' => null]);

        $this->actingAs($guru)
            ->get(route('guru.materi.unduh', ['penugasan' => $penugasan, 'materi' => $materi]))
            ->assertOk()
            ->assertDownload('bab-1.pdf');
    }

    public function test_guru_lain_tidak_bisa_mengelola_materi_kelas_orang(): void
    {
        $tahun = $this->tahun();
        $semester = $this->semester($tahun);
        $ratna = $this->akunGuru('Bu Ratna');
        $agus = $this->akunGuru('Pak Agus');
        $penugasan = $this->penugasan($tahun, $ratna);

        Storage::disk('local')->put('materi/abc.pdf', 'isi');
        $materi = $this->materi($penugasan, $semester, ['file_path' => 'materi/abc.pdf']);
        $par = ['penugasan' => $penugasan, 'materi' => $materi];

        $this->actingAs($agus);

        $this->get(route('guru.materi.index', ['penugasan' => $penugasan]))->assertForbidden();
        $this->get(route('guru.materi.create', ['penugasan' => $penugasan]))->assertForbidden();
        $this->post(route('guru.materi.store', ['penugasan' => $penugasan]), [
            'judul' => 'Selundupan',
            'link_url' => 'https://a.b/c',
        ])->assertForbidden();
        $this->get(route('guru.materi.edit', $par))->assertForbidden();
        $this->put(route('guru.materi.update', $par), ['judul' => 'Diubah'])->assertForbidden();
        $this->delete(route('guru.materi.destroy', $par))->assertForbidden();
        $this->get(route('guru.materi.unduh', $par))->assertForbidden();

        $this->assertDatabaseHas('materi', ['id' => $materi->id, 'judul' => 'Bab 1']);
        $this->assertDatabaseCount('materi', 1);
    }

    public function test_materi_kelas_lain_tidak_bisa_diakses_lewat_penugasan_sendiri(): void
    {
        $tahun = $this->tahun();
        $semester = $this->semester($tahun);
        $guru = $this->akunGuru('Bu Ratna');
        $penugasanA = $this->penugasan($tahun, $guru, 'X-A', 'MTK');
        $penugasanB = $this->penugasan($tahun, $guru, 'X-B', 'BIN');
        $materiB = $this->materi($penugasanB, $semester);
        $par = ['penugasan' => $penugasanA, 'materi' => $materiB];

        $this->actingAs($guru);

        $this->get(route('guru.materi.edit', $par))->assertNotFound();
        $this->put(route('guru.materi.update', $par), ['judul' => 'Diubah'])->assertNotFound();
        $this->delete(route('guru.materi.destroy', $par))->assertNotFound();
        $this->get(route('guru.materi.unduh', $par))->assertNotFound();

        $this->assertDatabaseHas('materi', ['id' => $materiB->id, 'judul' => 'Bab 1']);
    }
}