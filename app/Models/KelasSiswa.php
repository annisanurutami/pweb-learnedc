<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KelasSiswa extends Model
{
    protected $table = 'kelas_siswa';
    protected $guarded = [];

    public function kelas() { return $this->belongsTo(Kelas::class); }
    public function siswa() { return $this->belongsTo(Siswa::class); }
    public function tahunAjaran() { return $this->belongsTo(TahunAjaran::class); }
}