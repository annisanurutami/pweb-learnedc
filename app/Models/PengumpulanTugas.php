<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PengumpulanTugas extends Model
{
    protected $table = 'pengumpulan_tugas';
    protected $guarded = [];

    protected function casts(): array
    {
        return ['submitted_at' => 'datetime', 'nilai' => 'decimal:2'];
    }

    public function tugas() { return $this->belongsTo(Tugas::class, 'tugas_id'); }
    public function siswa() { return $this->belongsTo(Siswa::class, 'siswa_id'); }
}