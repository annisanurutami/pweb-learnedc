<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PresensiGuru extends Model
{
    protected $table = 'presensi_guru';
    protected $guarded = [];

    protected function casts(): array
    {
        return ['tanggal' => 'date'];
    }

    public function guru() { return $this->belongsTo(Guru::class, 'guru_id'); }
    public function jadwal() { return $this->belongsTo(JadwalPelajaran::class, 'jadwal_id'); }
}