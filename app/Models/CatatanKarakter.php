<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CatatanKarakter extends Model
{
    protected $table = 'catatan_karakter';
    protected $guarded = [];

    public function siswa() { return $this->belongsTo(Siswa::class, 'siswa_id'); }
    public function waliKelas() { return $this->belongsTo(Guru::class, 'wali_kelas_id'); }
    public function semester() { return $this->belongsTo(Semester::class, 'semester_id'); }
}