<?php

namespace App\Support;

use Carbon\Carbon;

class LeaveDuration
{
    public static function totalDays(?string $start, ?string $end): ?int
    {
        if ($start === null || $start === '' || $end === null || $end === '') {
            return null;
        }

        try {
            $s = Carbon::parse($start)->startOfDay();
            $e = Carbon::parse($end)->startOfDay();
        } catch (\Throwable) {
            return null;
        }

        if ($e->lt($s)) {
            return null;
        }

        return $s->diffInDays($e) + 1;
    }

    public static function label(?string $start, ?string $end): string
    {
        $days = self::totalDays($start, $end);
        if ($days === null) {
            return '—';
        }

        return $days === 1 ? '1 day' : "{$days} days";
    }
}
