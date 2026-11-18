<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditNilai extends Model
{
    protected $table = 'audit_nilai';
    protected $guarded = [];

    protected function casts(): array
    {
        return ['nilai_lama' => 'decimal:2', 'nilai_baru' => 'decimal:2'];
    }

    public function nilai() { return $this->belongsTo(Nilai::class, 'nilai_id'); }
    public function user() { return $this->belongsTo(User::class, 'user_id'); }
}