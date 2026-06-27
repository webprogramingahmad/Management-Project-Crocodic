<?php

namespace App\Support;

use App\Models\User;

class AccountDisplay
{
    public static function divisionLabel(User $user): string
    {
        $user->loadMissing('role', 'division');

        $roleKey = strtolower((string) ($user->role?->role ?? ''));

        if (in_array($roleKey, ['executive', 'director'], true)) {
            return '-';
        }

        $divisi = $user->division?->divisi;

        return $divisi ? ucfirst($divisi) : '-';
    }
}
