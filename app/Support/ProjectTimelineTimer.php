<?php

namespace App\Support;

use App\Models\Project;
use Carbon\Carbon;

class ProjectTimelineTimer
{
    public static function normalizedStatusClass(Project $project): string
    {
        $class = strtolower((string) ($project->status?->class ?? ''));
        if ($class !== '') {
            return $class;
        }

        $raw = strtolower((string) ($project->status->status ?? ''));
        if (str_contains($raw, 'to do') || str_contains($raw, 'todo') || str_contains($raw, 'not')) {
            return 'todo';
        }
        if (str_contains($raw, 'running')) {
            return 'running';
        }
        if (str_contains($raw, 'maintenance')) {
            return 'maintenance';
        }
        if (str_contains($raw, 'complete') || str_contains($raw, 'finish')) {
            return 'completed';
        }

        return '';
    }

    public static function todoStartBalanceSeconds(Project $project): ?int
    {
        if (self::normalizedStatusClass($project) !== 'todo' || !$project->start_date) {
            return null;
        }

        $start = Carbon::parse($project->start_date)->startOfDay()->timezone(config('app.timezone'));
        $now = now()->timezone(config('app.timezone'));

        return (int) ($start->getTimestamp() - $now->getTimestamp());
    }

    public static function runningEndBalanceSeconds(Project $project): ?int
    {
        if (self::normalizedStatusClass($project) !== 'running' || !$project->end_date) {
            return null;
        }

        $end = Carbon::parse($project->end_date)->endOfDay()->timezone(config('app.timezone'));
        $now = now()->timezone(config('app.timezone'));

        return (int) ($end->getTimestamp() - $now->getTimestamp());
    }

    public static function formatBalanceSeconds(?int $seconds): string
    {
        if ($seconds === null) {
            return '-';
        }

        $abs = abs($seconds);
        $h = intdiv($abs, 3600);
        $m = intdiv($abs % 3600, 60);
        $s = $abs % 60;
        $hms = str_pad((string) $h, 2, '0', STR_PAD_LEFT)
            . ':' . str_pad((string) $m, 2, '0', STR_PAD_LEFT)
            . ':' . str_pad((string) $s, 2, '0', STR_PAD_LEFT);

        return $seconds < 0 ? '-' . $hms : $hms;
    }

    public static function formatBalanceDays(?int $seconds): string
    {
        if ($seconds === null) {
            return '-';
        }

        $absDays = (int) ceil(abs($seconds) / 86400);
        $days = $seconds < 0 ? -$absDays : $absDays;

        return $days . ' hari';
    }
}
