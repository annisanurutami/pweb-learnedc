<?php

namespace App\Policies;

use App\Models\GuruMapel;
use App\Models\User;

class GuruMapelPolicy
{
    /** Guru hanya boleh mengelola penugasan (kelas dan mapel) miliknya sendiri. */
    public function kelola(User $user, GuruMapel $penugasan): bool
    {
        $guruId = $user->guru?->id;

        return $guruId !== null && (int) $guruId === (int) $penugasan->guru_id;
    }
}