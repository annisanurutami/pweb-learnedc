<?php

namespace Tests\Feature;

use App\Models\Guru;
use App\Models\Kelas;
use App\Models\Semester;
use App\Models\TahunAjaran;
use App\Models\User;
use App\Support\Menu;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TampilanLearnedcTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    /** @param array<int, string> $roles */
    private function akun(string $nama, array $roles): User
    {
        $user = User::factory()->create(['name' => $nama]);
        $user->assignRole($roles);

        return $user;
    }

    public function test_halaman_login_memakai_desain_dan_form_tetap_berfungsi(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee("LEARN'S EDC", false)
            ->assertSee('name="email"', false)
            ->assertSee('name="password"', false)
            ->assertSee('action="'.route('login').'"', false)
            ->assertSee('css/learnedc/login.css', false);
    }

    public function test_login_gagal_menampilkan_pesan_berbahasa_indonesia(): void
    {
        $this->akun('Bu Ratna', ['guru']);

        $this->from(route('login'))
            ->post(route('login'), ['email' => 'tidak@ada.test', 'password' => 'salah'])
            ->assertRedirect(route('login'));

        $this->get(route('login'))->assertSee('Email atau password salah.');
    }

    public function test_halaman_lupa_password_memakai_desain(): void
    {
        $this->get(route('password.request'))
            ->assertOk()
            ->assertSee('Kirim tautan reset password')
            ->assertSee('css/learnedc/login.css', false);
    }

    public function test_dashboard_admin_menampilkan_sidebar_lengkap_dan_peran(): void
    {
        $admin = $this->akun('Admin Sekolah', ['super_admin']);

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('sidebar-admin', false)
            ->assertSee('Super Admin')
            ->assertSee('Admin Sekolah')
            ->assertSee('Tahun Ajaran')
            ->assertSee('Penugasan Guru')
            ->assertSee('Jadwal Pelajaran')
            ->assertSee('Akun Siswa')
            ->assertSee('css/learnedc/admin.css', false)
            ->assertSee('js/learnedc/layout.js', false);
    }

    public function test_sidebar_guru_hanya_berisi_menu_guru(): void
    {
        $guru = $this->akun('Pak Agus', ['guru']);

        $this->actingAs($guru)
            ->get(route('guru.dashboard'))
            ->assertOk()
            ->assertSee('Kelas Saya')
            ->assertDontSee('Akun Guru')
            ->assertDontSee('Penugasan Guru')
            ->assertDontSee('Tahun Ajaran');
    }

    public function test_role_rangkap_tidak_menggandakan_menu(): void
    {
        $wali = $this->akun('Bu Ratna', ['guru', 'wali_kelas']);

        $html = $this->actingAs($wali)->get(route('walikelas.dashboard'))->assertOk()->getContent();

        $this->assertSame(1, substr_count($html, 'Kelas Saya'));
        $this->assertStringContainsString('Wali Kelas', $html);
    }

    public function test_menu_item_aktif_ditandai_sesuai_halaman(): void
    {
        $admin = $this->akun('Admin Sekolah', ['super_admin']);

        $this->actingAs($admin)
            ->get(route('admin.kelas.index'))
            ->assertOk()
            ->assertSee('sidebar-link active', false);

        $this->actingAs($admin)
            ->get(route('admin.penugasan.index'))
            ->assertOk()
            ->assertSee('sidebar-link active', false);
    }

    public function test_menu_untuk_mengembalikan_peran_dan_ikon_halaman_aktif(): void
    {
        $admin = $this->akun('Admin Sekolah', ['super_admin']);
        $this->actingAs($admin)->get(route('admin.kelas.index'));

        $menu = Menu::untuk($admin);

        $this->assertSame('Super Admin', $menu['peran']);
        $this->assertSame('fa-people-roof', $menu['ikon']);
        $this->assertSame('MENU UTAMA', $menu['seksi'][0]['judul']);
        $this->assertSame('Beranda', $menu['seksi'][0]['item'][0]['label']);
    }

    public function test_tombol_keluar_mengirim_post_ke_route_logout(): void
    {
        $guru = $this->akun('Pak Agus', ['guru']);

        $this->actingAs($guru)
            ->get(route('guru.dashboard'))
            ->assertSee('action="'.route('logout').'"', false)
            ->assertSee('Ya, keluar');
    }

    public function test_dashboard_admin_menghitung_data_sungguhan(): void
    {
        $admin = $this->akun('Admin Sekolah', ['super_admin']);
        $tahun = TahunAjaran::create([
            'nama' => '2026/2027',
            'tgl_mulai' => '2026-07-13',
            'tgl_selesai' => '2027-06-26',
            'is_active' => true,
        ]);
        Semester::create([
            'tahun_ajaran_id' => $tahun->id,
            'nama' => 'Ganjil',
            'tgl_mulai' => '2026-07-13',
            'tgl_selesai' => '2026-12-19',
            'is_active' => true,
        ]);
        Kelas::create(['tahun_ajaran_id' => $tahun->id, 'nama_kelas' => 'X-A', 'iuran_kas' => 0]);
        Kelas::create(['tahun_ajaran_id' => $tahun->id, 'nama_kelas' => 'X-B', 'iuran_kas' => 0]);
        $akunGuru = $this->akun('Bu Ratna', ['guru']);
        Guru::create(['user_id' => $akunGuru->id, 'nama' => 'Bu Ratna']);

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Semester aktif: Ganjil 2026/2027')
            ->assertSeeInOrder(['Guru terdaftar', '<strong>1</strong>'], false)
            ->assertSeeInOrder(['Kelas aktif', '<strong>2</strong>'], false);
    }

    public function test_dashboard_guru_tanpa_data_guru_tidak_error(): void
    {
        $guru = $this->akun('Pak Tanpa Data', ['guru']);

        $this->actingAs($guru)
            ->get(route('guru.dashboard'))
            ->assertOk()
            ->assertSee('Kelas diampu');
    }

    public function test_halaman_profil_memakai_layout_baru_dan_terjemahan(): void
    {
        $guru = $this->akun('Pak Agus', ['guru']);

        $this->actingAs($guru)
            ->get(route('profile.edit'))
            ->assertOk()
            ->assertSee('sidebar-admin', false)
            ->assertSee('Informasi Profil');
    }

    public function test_pesan_flash_memakai_gaya_notice_desain(): void
    {
        $admin = $this->akun('Admin Sekolah', ['super_admin']);

        $this->actingAs($admin)
            ->withSession(['success' => 'Data tersimpan.', 'error' => 'Gagal menyimpan.'])
            ->get(route('admin.kelas.index'))
            ->assertSee('class="notice"', false)
            ->assertSee('class="notice error"', false)
            ->assertSee('Data tersimpan.')
            ->assertSee('Gagal menyimpan.');
    }
}
