<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JadwalPelajaran extends Model
{
    protected $table = 'jadwal_pelajaran';
    protected $guarded = [];

    public function guruMapel() { return $this->belongsTo(GuruMapel::class); }
    public function semester() { return $this->belongsTo(Semester::class); }
    public function hari() { return $this->belongsTo(Hari::class, 'hari_id'); }
}