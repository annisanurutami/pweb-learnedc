<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Materi extends Model
{
    protected $table = 'materi';
    protected $guarded = [];

    public function guruMapel() { return $this->belongsTo(GuruMapel::class, 'guru_mapel_id'); }
    public function semester() { return $this->belongsTo(Semester::class, 'semester_id'); }
}