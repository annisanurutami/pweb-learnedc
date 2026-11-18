<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SoalUjian extends Model
{
    protected $table = 'soal_ujian';
    protected $guarded = [];

    protected function casts(): array
    {
        return ['opsi' => 'array', 'bobot' => 'decimal:2'];
    }

    public function ujian() { return $this->belongsTo(Ujian::class, 'ujian_id'); }
    public function jawaban() { return $this->hasMany(JawabanUjian::class, 'soal_id'); }
}