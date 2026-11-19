<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    /** Route dashboard milik tiap role. */
    private const DASHBOARD = [
        'super_admin' => 'admin.dashboard',
        'kepala_sekolah' => 'kepsek.dashboard',
        'wali_kelas' => 'walikelas.dashboard',
        'guru' => 'guru.dashboard',
        'siswa' => 'siswa.dashboard',
        'bendahara' => 'bendahara.dashboard',
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->seed(RoleSeeder::class);
    }

    /** @param array<int, string> $roles */
    private function akun(array $roles): User
    {
        $user = User::factory()->create();
        $user->syncRoles($roles);

        return $user;
    }

    public function test_tamu_diarahkan_ke_login(): void
    {
        $this->get('/dashboard')->assertRedirect(route('login'));
        $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
    }

    public function test_tiap_role_hanya_bisa_membuka_dashboard_miliknya(): void
    {
        foreach (self::DASHBOARD as $role => $routeSendiri) {
            $user = $this->akun([$role]);

            foreach (self::DASHBOARD as $roleLain => $route) {
                // Wali kelas juga boleh membuka area guru.
                $boleh = $route === $routeSendiri
                    || ($role === 'wali_kelas' && $roleLain === 'guru');

                $this->actingAs($user)
                    ->get(route($route))
                    ->assertStatus($boleh ? 200 : 403);
            }
        }
    }

    public function test_dashboard_mengarahkan_sesuai_role(): void
    {
        foreach (self::DASHBOARD as $role => $route) {
            $this->actingAs($this->akun([$role]))
                ->get('/dashboard')
                ->assertRedirect(route($route));
        }
    }

    public function test_akun_dengan_dua_role_masuk_ke_role_berprioritas(): void
    {
        $guruWali = $this->akun(['guru', 'wali_kelas']);
        $this->actingAs($guruWali)->get('/dashboard')->assertRedirect(route('walikelas.dashboard'));
        $this->actingAs($guruWali)->get(route('guru.dashboard'))->assertOk();
        $this->actingAs($guruWali)->get(route('admin.dashboard'))->assertForbidden();

        $siswaBendahara = $this->akun(['siswa', 'bendahara']);
        $this->actingAs($siswaBendahara)->get('/dashboard')->assertRedirect(route('siswa.dashboard'));
        $this->actingAs($siswaBendahara)->get(route('bendahara.dashboard'))->assertOk();
    }

    public function test_akun_nonaktif_dikeluarkan(): void
    {
        $user = User::factory()->create(['is_active' => false]);
        $user->syncRoles(['siswa']);

        $this->actingAs($user)
            ->get(route('siswa.dashboard'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }
}