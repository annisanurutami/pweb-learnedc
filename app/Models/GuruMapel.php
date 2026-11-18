<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GuruMapel extends Model
{
    protected $table = 'guru_mapel';
    protected $guarded = [];

    public function guru()
    {
        return $this->belongsTo(Guru::class);
    }
    public function mapel()
    {
        return $this->belongsTo(MataPelajaran::class, 'mapel_id');
    }
    public function kelas()
    {
        return $this->belongsTo(Kelas::class);
    }
    public function jadwalPelajaran()
    {
        return $this->hasMany(JadwalPelajaran::class);
    }
    public function materi()
    {
        return $this->hasMany(Materi::class, 'guru_mapel_id');
    }
    public function tugas()
    {
        return $this->hasMany(Tugas::class, 'guru_mapel_id');
    }
    public function ujian()
    {
        return $this->hasMany(Ujian::class, 'guru_mapel_id');
    }
}