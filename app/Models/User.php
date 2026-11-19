<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, HasRoles, Notifiable;

    protected $fillable = ['name', 'email', 'password'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function guru(): HasOne
    {
        return $this->hasOne(Guru::class);
    }

    public function siswa(): HasOne
    {
        return $this->hasOne(Siswa::class);
    }

    public function notifikasi(): HasMany
    {
        return $this->hasMany(Notifikasi::class, 'user_id');
    }

    /** Nama route dashboard sesuai role berprioritas tertinggi milik akun. */
    public function dashboardRouteName(): string
    {
        $peta = [
            'super_admin' => 'admin.dashboard',
            'kepala_sekolah' => 'kepsek.dashboard',
            'wali_kelas' => 'walikelas.dashboard',
            'guru' => 'guru.dashboard',
            'siswa' => 'siswa.dashboard',
            'bendahara' => 'bendahara.dashboard',
        ];

        foreach ($peta as $role => $route) {
            if ($this->hasRole($role)) {
                return $route;
            }
        }

        return 'profile.edit';
    }
}