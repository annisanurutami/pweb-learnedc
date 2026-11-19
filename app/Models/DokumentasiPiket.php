<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DokumentasiPiket extends Model
{
    protected $table = 'dokumentasi_piket';
    protected $guarded = [];

    protected function casts(): array
    {
        return ['tanggal' => 'date'];
    }

    public function jadwalPiket() { return $this->belongsTo(JadwalPiket::class, 'jadwal_piket_id'); }
}