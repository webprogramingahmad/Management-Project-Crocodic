<?php

namespace App\Support;

use Illuminate\Support\Str;

/**
 * Human-readable role labels for UI only. DB keys: executive, director, staff (legacy keys kept for display fallback).
 */
class RoleDisplay
{
    private const MAP = [
        'executive' => 'Executive',
        'director' => 'Director',
        'staff' => 'Staff',
        'admin' => 'Executive',
        'project director' => 'Director',
        'user' => 'Staff',
    ];

    public static function label(?string $roleKey): string
    {
        if ($roleKey === null || $roleKey === '' || $roleKey === '-') {
            return '-';
        }

        $normalized = strtolower(trim($roleKey));

        return self::MAP[$normalized] ?? Str::title($roleKey);
    }
}
