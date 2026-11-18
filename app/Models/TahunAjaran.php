<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TahunAjaran extends Model
{
    protected $table = 'tahun_ajaran';
    protected $guarded = [];

    protected function casts(): array
    {
        return ['tgl_mulai' => 'date', 'tgl_selesai' => 'date', 'is_active' => 'boolean'];
    }

    public function semester() { return $this->hasMany(Semester::class); }
    public function kelas() { return $this->hasMany(Kelas::class); }
}