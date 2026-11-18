<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Guru extends Model
{
    protected $table = 'guru';
    protected $guarded = [];

    public function user() { return $this->belongsTo(User::class); }
    public function guruMapel() { return $this->hasMany(GuruMapel::class); }
    public function kelasWali() { return $this->hasMany(Kelas::class, 'wali_kelas_id'); }
}