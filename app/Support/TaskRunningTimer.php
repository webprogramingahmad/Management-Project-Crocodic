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

    /** Status task = kolom Review. */
    public static function isReviewStatus(?\App\Models\StatusTask $status): bool
    {
        if (!$status) {
            return false;
        }

        if (($status->class ?? '') === 'review') {
            return true;
        }

        return strcasecmp((string) ($status->status ?? ''), 'Review') === 0;
    }

    /** Status task = kolom Revision. */
    public static function isRevisionStatus(?\App\Models\StatusTask $status): bool
    {
        if (!$status) {
            return false;
        }

        if (($status->class ?? '') === 'revision') {
            return true;
        }

        return strcasecmp((string) ($status->status ?? ''), 'Revision') === 0;
    }

    public static function isCompleteStatus(?\App\Models\StatusTask $status): bool
    {
        if (!$status) {
            return false;
        }

        if (($status->class ?? '') === 'complete') {
            return true;
        }

        return strcasecmp((string) ($status->status ?? ''), 'Complete') === 0;
    }

    /**
     * Deadline absolut untuk countdown (timezone app).
     * — Revision: revision_deadline_at (ditentukan director).
     * — In progress: dari running_started_at + level task.
     */
    public static function deadlineFor(Task $task): ?Carbon
    {
        $task->loadMissing(['status', 'difficulty']);

        if (self::isRevisionStatus($task->status) && $task->revision_deadline_at) {
            return $task->revision_deadline_at->copy()->timezone(config('app.timezone'));
        }

        if (!self::isInProgressStatus($task->status)) {
            return null;
        }

        return self::deadlineFromProgressStart($task);
    }

    private static function deadlineFromProgressStart(Task $task): ?Carbon
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

    /** Deadline untuk tampilan beku di kolom Review (siklus pertama atau setelah revision). */
    private static function reviewFreezeDeadline(Task $task): ?Carbon
    {
        if (!self::isReviewStatus($task->status) || !$task->running_review_at) {
            return null;
        }

        if ($task->revision_deadline_at) {
            return $task->revision_deadline_at->copy()->timezone(config('app.timezone'));
        }

        return self::deadlineFromProgressStart($task);
    }

    /** Timer hidup: In progress (level task) atau Revision (deadline director). */
    public static function shouldShowLiveTimer(Task $task): bool
    {
        $task->loadMissing(['status', 'difficulty']);

        if (self::isRevisionStatus($task->status)) {
            return $task->revision_deadline_at !== null;
        }

        if (!self::isInProgressStatus($task->status)) {
            return false;
        }

        return self::deadlineFromProgressStart($task) !== null;
    }

    /**
     * Sisa waktu (ms) pada saat task masuk Review — tampilan beku di kartu.
     */
    public static function frozenRemainMsForReview(Task $task): ?int
    {
        $task->loadMissing(['status', 'difficulty']);

        if (!self::isReviewStatus($task->status) || !$task->running_review_at) {
            return null;
        }

        $deadline = self::reviewFreezeDeadline($task);
        if (!$deadline) {
            return null;
        }

        $reviewAt = $task->running_review_at->copy()->timezone(config('app.timezone'));

        return (int) round(($deadline->getTimestamp() - $reviewAt->getTimestamp()) * 1000);
    }

    public static function shouldShowTimer(Task $task): bool
    {
        return self::shouldShowLiveTimer($task) || self::frozenRemainMsForReview($task) !== null;
    }

    public static function deadlineIsoString(Task $task): ?string
    {
        $d = self::deadlineFor($task);

        return $d?->toIso8601String();
    }
}
