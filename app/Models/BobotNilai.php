<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BobotNilai extends Model
{
    protected $table = 'bobot_nilai';
    protected $guarded = [];

    public function semester() { return $this->belongsTo(Semester::class, 'semester_id'); }
}