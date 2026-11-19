<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KasPengeluaran extends Model
{
    protected $table = 'kas_pengeluaran';
    protected $guarded = [];

    protected function casts(): array
    {
        return ['tanggal' => 'date', 'jumlah' => 'decimal:2'];
    }

    public function kelas() { return $this->belongsTo(Kelas::class, 'kelas_id'); }
    public function pencatat() { return $this->belongsTo(User::class, 'dicatat_oleh'); }
}