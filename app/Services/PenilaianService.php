<?php

namespace App\Services;

use App\Models\AuditNilai;
use App\Models\Nilai;
use App\Models\PengumpulanTugas;
use App\Models\Siswa;
use App\Models\Tugas;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class PenilaianService
{
    /**
     * Satu-satunya jalur penulisan nilai tugas.
     *
     * - nilai mentah  : pengumpulan_tugas.nilai dan catatan_guru
     * - sumber laporan: tabel nilai (jenis tugas_harian, sumber_id = id tugas)
     * - jejak ubah    : audit_nilai, hanya jika nilainya baru atau berbeda dari sebelumnya
     */
    public function simpanNilaiTugas(Tugas $tugas, Siswa $siswa, float $nilai, ?string $catatan, User $oleh): Nilai
    {
        return DB::transaction(function () use ($tugas, $siswa, $nilai, $catatan, $oleh) {
            // Siswa yang belum mengumpulkan tetap boleh dinilai: baris dibuat tanpa submitted_at.
            $pengumpulan = PengumpulanTugas::firstOrNew([
                'tugas_id' => $tugas->id,
                'siswa_id' => $siswa->id,
            ]);
            $pengumpulan->nilai = $nilai;
            $pengumpulan->catatan_guru = $catatan;
            $pengumpulan->save();

            $baris = Nilai::firstOrNew([
                'siswa_id' => $siswa->id,
                'guru_mapel_id' => $tugas->guru_mapel_id,
                'semester_id' => $tugas->semester_id,
                'jenis' => 'tugas_harian',
                'sumber_id' => $tugas->id,
            ]);

            $nilaiLama = $baris->exists ? (float) $baris->nilai : null;

            $baris->nilai = $nilai;
            $baris->save();

            if ($nilaiLama === null || abs($nilaiLama - $nilai) >= 0.005) {
                AuditNilai::create([
                    'nilai_id' => $baris->id,
                    'user_id' => $oleh->id,
                    'nilai_lama' => $nilaiLama,
                    'nilai_baru' => $nilai,
                ]);
            }

            return $baris;
        });
    }
}