<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kelas extends Model
{
    protected $table = 'kelas';
    protected $guarded = [];

    public function tahunAjaran() { return $this->belongsTo(TahunAjaran::class); }
    public function waliKelas() { return $this->belongsTo(Guru::class, 'wali_kelas_id'); }
    public function kelasSiswa() { return $this->hasMany(KelasSiswa::class); }
    public function guruMapel() { return $this->hasMany(GuruMapel::class); }

    public function siswa()
    {
        return $this->belongsToMany(Siswa::class, 'kelas_siswa', 'kelas_id', 'siswa_id')
            ->withPivot('tahun_ajaran_id')
            ->withTimestamps();
    }
}