<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PresensiSiswa extends Model
{
    protected $table = 'presensi_siswa';
    protected $guarded = [];

    protected function casts(): array
    {
        return ['tanggal' => 'date'];
    }

    public function siswa() { return $this->belongsTo(Siswa::class, 'siswa_id'); }
    public function kelas() { return $this->belongsTo(Kelas::class, 'kelas_id'); }
    public function jadwal() { return $this->belongsTo(JadwalPelajaran::class, 'jadwal_id'); }
    public function pencatat() { return $this->belongsTo(User::class, 'dicatat_oleh'); }
}