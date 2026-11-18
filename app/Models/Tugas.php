<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tugas extends Model
{
    protected $table = 'tugas';
    protected $guarded = [];

    protected function casts(): array
    {
        return ['deadline' => 'datetime'];
    }

    public function guruMapel() { return $this->belongsTo(GuruMapel::class, 'guru_mapel_id'); }
    public function semester() { return $this->belongsTo(Semester::class, 'semester_id'); }
    public function pengumpulan() { return $this->hasMany(PengumpulanTugas::class, 'tugas_id'); }

    public function sudahLewatDeadline(): bool
    {
        return now()->greaterThan($this->deadline);
    }
}