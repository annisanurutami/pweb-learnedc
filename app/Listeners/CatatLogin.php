<?php

namespace App\Listeners;

use App\Models\LogLogin;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Str;

/**
 * Mencatat setiap login ke tabel log_login. Laravel menemukan listener ini otomatis
 * dari tipe parameter handle(), tidak perlu didaftarkan.
 */
class CatatLogin
{
    public function handle(Login $event): void
    {
        // Gagal mencatat tidak boleh menggagalkan login.
        rescue(fn() => LogLogin::create([
            'user_id' => $event->user->getAuthIdentifier(),
            'ip_address' => request()->ip(),
            'user_agent' => Str::limit((string) request()->userAgent(), 255, ''),
            'logged_at' => now(),
        ]));
    }
}