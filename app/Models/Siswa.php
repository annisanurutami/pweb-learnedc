<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Siswa extends Model
{
    protected $table = 'siswa';
    protected $guarded = [];

    protected function casts(): array
    {
        return ['tgl_lahir' => 'date'];
    }

    public function user() { return $this->belongsTo(User::class); }
    public function kelasSiswa() { return $this->hasMany(KelasSiswa::class); }
}