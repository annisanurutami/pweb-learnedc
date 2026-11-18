<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rapor extends Model
{
    protected $table = 'rapor';
    protected $guarded = [];

    protected function casts(): array
    {
        return ['tgl_cetak' => 'date'];
    }

    public function siswa() { return $this->belongsTo(Siswa::class, 'siswa_id'); }
    public function kelas() { return $this->belongsTo(Kelas::class, 'kelas_id'); }
    public function semester() { return $this->belongsTo(Semester::class, 'semester_id'); }
}