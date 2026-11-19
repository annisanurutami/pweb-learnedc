<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (['super_admin', 'kepala_sekolah', 'guru', 'wali_kelas', 'bendahara', 'siswa'] as $nama) {
            Role::findOrCreate($nama, 'web');
        }
    }
}