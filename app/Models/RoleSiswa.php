<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoleSiswa extends Model
{
    protected $table = 'role_siswa';
    protected $guarded = [];

    public function kelas() { return $this->belongsTo(Kelas::class, 'kelas_id'); }
    public function siswa() { return $this->belongsTo(Siswa::class, 'siswa_id'); }
    public function semester() { return $this->belongsTo(Semester::class, 'semester_id'); }
}