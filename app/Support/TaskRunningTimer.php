<?php

namespace App\Support;

use App\Models\Task;
use Carbon\Carbon;

class TaskRunningTimer
{
    /**
     * Sisa/lebih waktu siklus In progress (detik).
     * Positif = masih sisa waktu, negatif = minus.
     */
    public static function progressBalanceSeconds(Task $task): ?int
    {
        $task->loadMissing(['status', 'difficulty']);

        // Sesuai UI rule: hasil In Progress baru ditampilkan setelah task masuk Review.
        if (!$task->running_review_at) {
            return null;
        }

        if (!$task->running_started_at && !$task->created_at) {
            return null;
        }

        $deadline = self::deadlineFromProgressStart($task);
        if (!$deadline) {
            return null;
        }

        $endAt = $task->running_review_at->copy()->timezone(config('app.timezone'));

        return (int) ($deadline->getTimestamp() - $endAt->getTimestamp());
    }

    /**
     * @return array<int, array{cycle_number:int,balance_seconds:?int,is_active:bool}>
     */
    public static function revisionCycleBalances(Task $task): array
    {
        $task->loadMissing('revisionCycles');

        return $task->revisionCycles
            // Sesuai UI rule: hasil Revision baru ditampilkan setelah kembali ke Review.
            ->filter(fn ($cycle) => $cycle->exited_revision_at !== null)
            ->sortBy('cycle_number')
            ->map(function ($cycle): array {
                $balance = null;
                if ($cycle->deadline_at) {
                    $endAt = $cycle->exited_revision_at->copy()->timezone(config('app.timezone'));
                    $balance = (int) ($cycle->deadline_at->copy()->timezone(config('app.timezone'))->getTimestamp() - $endAt->getTimestamp());
                }

                return [
                    'cycle_number' => (int) $cycle->cycle_number,
                    'balance_seconds' => $balance,
                    'is_active' => $cycle->exited_revision_at === null,
                ];
            })
            ->values()
            ->all();
    }

    /**
     * Apakah task masuk Review tepat waktu (<= deadline level task)?
     * Independen dari status saat ini, sehingga task yang sudah Complete pun tetap terhitung.
     * Mengembalikan null jika task belum pernah masuk Review atau deadline tidak bisa dihitung.
     */
    public static function reviewedOnTime(Task $task): ?bool
    {
        $task->loadMissing('difficulty');

        if (!$task->running_review_at) {
            return null;
        }

        $deadline = self::deadlineFromProgressStart($task);
        if (!$deadline) {
            return null;
        }

        $reviewAt = $task->running_review_at->copy()->timezone(config('app.timezone'));

        return $reviewAt->getTimestamp() <= $deadline->getTimestamp();
    }

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
        if (!$task->running_started_at && !$task->created_at) {
            return null;
        }

        $level = $task->difficulty?->difficulty;
        if (!$level) {
            return null;
        }

        $startSource = $task->running_started_at ?? $task->created_at;
        $start = $startSource->copy()->timezone(config('app.timezone'));

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
        $task->loadMissing(['status', 'difficulty', 'revisionCycles']);

        if (!self::isReviewStatus($task->status) || !$task->running_review_at) {
            // Untuk siklus revision selesai, hasil freeze diambil dari cycle terakhir
            // meski running_review_at tidak diperbarui.
            if (!self::isReviewStatus($task->status)) {
                return null;
            }
        }

        $latestClosedRevision = $task->revisionCycles
            ->whereNotNull('exited_revision_at')
            ->sortByDesc('cycle_number')
            ->first();

        if ($latestClosedRevision && $latestClosedRevision->deadline_at && $latestClosedRevision->exited_revision_at) {
            return (int) round((
                $latestClosedRevision->deadline_at->copy()->timezone(config('app.timezone'))->getTimestamp()
                - $latestClosedRevision->exited_revision_at->copy()->timezone(config('app.timezone'))->getTimestamp()
            ) * 1000);
        }

        if (!$task->running_review_at) {
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
