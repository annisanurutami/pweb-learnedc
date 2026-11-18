<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ujian extends Model
{
    protected $table = 'ujian';
    protected $guarded = [];

    protected function casts(): array
    {
        return ['waktu_mulai' => 'datetime', 'waktu_selesai' => 'datetime'];
    }

    public function guruMapel() { return $this->belongsTo(GuruMapel::class, 'guru_mapel_id'); }
    public function semester() { return $this->belongsTo(Semester::class, 'semester_id'); }
    public function soal() { return $this->hasMany(SoalUjian::class, 'ujian_id'); }
    public function pengerjaan() { return $this->hasMany(PengerjaanUjian::class, 'ujian_id'); }

    public function sedangBuka(): bool
    {
        return now()->between($this->waktu_mulai, $this->waktu_selesai);
    }
}