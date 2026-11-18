<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PengerjaanUjian extends Model
{
    protected $table = 'pengerjaan_ujian';
    protected $guarded = [];

    protected function casts(): array
    {
        return ['mulai_at' => 'datetime', 'selesai_at' => 'datetime', 'nilai_akhir' => 'decimal:2'];
    }

    public function ujian() { return $this->belongsTo(Ujian::class, 'ujian_id'); }
    public function siswa() { return $this->belongsTo(Siswa::class, 'siswa_id'); }
    public function jawaban() { return $this->hasMany(JawabanUjian::class, 'pengerjaan_ujian_id'); }
}