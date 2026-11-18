<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MataPelajaran extends Model
{
    protected $table = 'mata_pelajaran';
    protected $guarded = [];

    public function guruMapel() { return $this->hasMany(GuruMapel::class, 'mapel_id'); }
}