<?php

namespace App\Support;

use App\Models\Task;
use Carbon\Carbon;

class TaskRunningTimer
{
    /** Status task = sedang jalan (kolom In progress). */
    public static function isInProgressStatus(?\App\Models\StatusTask $status): bool
    {
        if (!$status) {
            return false;
        }

        if (($status->class ?? '') === 'progress') {
            return true;
        }

        return strcasecmp((string) ($status->status ?? ''), 'In progress') === 0;
    }

    /**
     * Deadline absolut untuk task yang sedang running (timezone app).
     * Low: +2 jam, Medium: +6 jam, High: akhir hari kalender saat mulai running.
     */
    public static function deadlineFor(Task $task): ?Carbon
    {
        if (!$task->running_started_at) {
            return null;
        }

        $level = $task->difficulty?->difficulty;
        if (!$level) {
            return null;
        }

        $start = $task->running_started_at->copy()->timezone(config('app.timezone'));

        return match (strtolower((string) $level)) {
            'low' => $start->copy()->addHours(2),
            'medium' => $start->copy()->addHours(6),
            'high' => $start->copy()->endOfDay(),
            default => null,
        };
    }

    public static function shouldShowTimer(Task $task): bool
    {
        $task->loadMissing(['status', 'difficulty']);

        if (!self::isInProgressStatus($task->status)) {
            return false;
        }

        return self::deadlineFor($task) !== null;
    }

    public static function deadlineIsoString(Task $task): ?string
    {
        $d = self::deadlineFor($task);

        return $d?->toIso8601String();
    }
}
