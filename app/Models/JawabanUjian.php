<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JawabanUjian extends Model
{
    protected $table = 'jawaban_ujian';
    protected $guarded = [];

    protected function casts(): array
    {
        return ['skor' => 'decimal:2'];
    }

    public function pengerjaan() { return $this->belongsTo(PengerjaanUjian::class, 'pengerjaan_ujian_id'); }
    public function soal() { return $this->belongsTo(SoalUjian::class, 'soal_id'); }
}