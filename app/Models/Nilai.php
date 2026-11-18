<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Nilai extends Model
{
    protected $table = 'nilai';
    protected $guarded = [];

    protected function casts(): array
    {
        return ['nilai' => 'decimal:2'];
    }

    public function siswa() { return $this->belongsTo(Siswa::class, 'siswa_id'); }
    public function guruMapel() { return $this->belongsTo(GuruMapel::class, 'guru_mapel_id'); }
    public function semester() { return $this->belongsTo(Semester::class, 'semester_id'); }
    public function audit() { return $this->hasMany(AuditNilai::class, 'nilai_id'); }
}