<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Semester extends Model
{
    protected $table = 'semester';
    protected $guarded = [];

    protected function casts(): array
    {
        return ['tgl_mulai' => 'date', 'tgl_selesai' => 'date', 'is_active' => 'boolean'];
    }

    public function tahunAjaran() { return $this->belongsTo(TahunAjaran::class); }
}