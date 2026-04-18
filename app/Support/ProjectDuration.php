<?php

namespace App\Support;

use Carbon\Carbon;

class ProjectDuration
{
    /**
     * Calendar-based breakdown: count full months from start (using Carbon::addMonth),
     * then remaining days until end (same as diffInDays).
     *
     * @return array{months: int, days: int}|null null if invalid range
     */
    public static function breakdown(?string $start, ?string $end): ?array
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

        $months = 0;
        $cursor = $s->copy();

        while (true) {
            $next = $cursor->copy()->addMonth();
            if ($next->gt($e)) {
                break;
            }
            $cursor = $next;
            $months++;
        }

        $days = (int) $cursor->diffInDays($e);

        return ['months' => $months, 'days' => $days];
    }

    public static function label(?string $start, ?string $end): string
    {
        $b = self::breakdown($start, $end);
        if ($b === null) {
            return '—';
        }

        if ($b['months'] === 0 && $b['days'] === 0) {
            return '1 day';
        }

        return self::formatEnglish($b['months'], $b['days']);
    }

    private static function formatEnglish(int $months, int $days): string
    {
        $parts = [];
        if ($months > 0) {
            $parts[] = $months === 1 ? '1 month' : "{$months} months";
        }
        if ($days > 0) {
            $parts[] = $days === 1 ? '1 day' : "{$days} days";
        }

        return $parts !== [] ? implode(', ', $parts) : '0 days';
    }
}
