<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LogLogin extends Model
{
    protected $table = 'log_login';
    protected $guarded = [];
    public $timestamps = false;

    protected function casts(): array
    {
        return ['logged_at' => 'datetime'];
    }

    public function user() { return $this->belongsTo(User::class, 'user_id'); }
}