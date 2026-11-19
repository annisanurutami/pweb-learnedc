<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notifikasi extends Model
{
    protected $table = 'notifikasi';
    protected $guarded = [];

    protected function casts(): array
    {
        return ['is_read' => 'boolean'];
    }

    public function user() { return $this->belongsTo(User::class, 'user_id'); }

    public function scopeBelumDibaca($query)
    {
        return $query->where('is_read', false);
    }
}