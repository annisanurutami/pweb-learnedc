<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JadwalPiket extends Model
{
    protected $table = 'jadwal_piket';
    protected $guarded = [];

    public function kelas() { return $this->belongsTo(Kelas::class, 'kelas_id'); }
    public function siswa() { return $this->belongsTo(Siswa::class, 'siswa_id'); }
    public function hari() { return $this->belongsTo(Hari::class, 'hari_id'); }
    public function semester() { return $this->belongsTo(Semester::class, 'semester_id'); }
    public function dokumentasi() { return $this->hasMany(DokumentasiPiket::class, 'jadwal_piket_id'); }
}