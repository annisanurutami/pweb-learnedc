<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Hari extends Model
{
    protected $table = 'hari';
    protected $guarded = [];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function jadwalPelajaran() { return $this->hasMany(JadwalPelajaran::class, 'hari_id'); }
}