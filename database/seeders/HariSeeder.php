<?php

namespace Database\Seeders;

use App\Models\Hari;
use Illuminate\Database\Seeder;

class HariSeeder extends Seeder
{
    public function run(): void
    {
        $daftar = [1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu'];

        foreach ($daftar as $urutan => $nama) {
            Hari::updateOrCreate(
                ['nama_hari' => $nama],
                ['urutan' => $urutan, 'is_active' => $urutan <= 5],
            );
        }
    }
}