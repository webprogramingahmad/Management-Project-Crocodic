<?php

namespace App\Support;

use App\Models\Task;
use App\Models\TaskPhoto;
use App\Models\TaskRevisionCycle;
use App\Models\TaskSubmission;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TaskSubmissionManager
{
    /**
     * @return array<string, mixed>
     */
    public static function buildPayloadForTask(Task $task): array
    {
        $task->loadMissing([
            'user:id,name',
            'project:id,name',
            'submissions.photos',
            'submissions.submitter:id,name',
            'revisionCycles.photos',
        ]);

        $work = $task->submissions
            ->firstWhere('type', TaskSubmission::TYPE_WORK);

        $cycles = $task->revisionCycles
            ->sortBy('cycle_number')
            ->map(function (TaskRevisionCycle $cycle) use ($task) {
                $staffSubmission = $task->submissions
                    ->first(fn ($s) => $s->type === TaskSubmission::TYPE_REVISION
                        && (int) $s->cycle_number === (int) $cycle->cycle_number);

                return [
                    'cycle_number' => (int) $cycle->cycle_number,
                    'director' => [
                        'notes' => $cycle->notes,
                        'links' => $cycle->links,
                        'revision_hours' => $cycle->revision_hours,
                        'entered_at' => $cycle->entered_revision_at?->toIso8601String(),
                        'deadline_at' => $cycle->deadline_at?->toIso8601String(),
                        'photos' => self::mapPhotos($cycle->photos),
                    ],
                    'staff_submission' => $staffSubmission ? self::mapSubmission($staffSubmission) : null,
                    'timing' => $staffSubmission ? self::buildRevisionTiming($cycle) : null,
                ];
            })
            ->values()
            ->all();

        return [
            'meta' => [
                'task_name' => $task->name,
                'owner_name' => $task->user?->name,
                'project_name' => $task->project?->name ?? 'Stand By',
            ],
            'work_submission' => $work ? array_merge(
                self::mapSubmission($work),
                ['timing' => self::buildWorkTiming($task)]
            ) : null,
            'revision_cycles' => $cycles,
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    private static function buildWorkTiming(Task $task): ?array
    {
        $balance = TaskRunningTimer::progressBalanceSeconds($task);
        if ($balance === null || ! $task->running_review_at) {
            return null;
        }

        $start = $task->running_started_at ?? $task->created_at;
        if (! $start) {
            return null;
        }

        $end = $task->running_review_at->copy()->timezone(config('app.timezone'));
        $startTz = $start->copy()->timezone(config('app.timezone'));
        $used = (int) ($end->getTimestamp() - $startTz->getTimestamp());

        return self::formatTimingPayload($used, $balance);
    }

    /**
     * @return array<string, mixed>|null
     */
    private static function buildRevisionTiming(TaskRevisionCycle $cycle): ?array
    {
        if (! $cycle->entered_revision_at || ! $cycle->exited_revision_at) {
            return null;
        }

        $start = $cycle->entered_revision_at->copy()->timezone(config('app.timezone'));
        $end = $cycle->exited_revision_at->copy()->timezone(config('app.timezone'));
        $used = (int) ($end->getTimestamp() - $start->getTimestamp());

        $balance = null;
        if ($cycle->deadline_at) {
            $balance = (int) (
                $cycle->deadline_at->copy()->timezone(config('app.timezone'))->getTimestamp()
                - $end->getTimestamp()
            );
        }

        return self::formatTimingPayload($used, $balance);
    }

    /**
     * @return array<string, mixed>
     */
    private static function formatTimingPayload(int $usedSeconds, ?int $balanceSeconds): array
    {
        $allocated = $balanceSeconds !== null
            ? $usedSeconds + $balanceSeconds
            : null;

        return [
            'used_seconds' => max(0, $usedSeconds),
            'allocated_seconds' => $allocated !== null ? max(0, $allocated) : null,
            'balance_seconds' => $balanceSeconds,
            'is_overdue' => $balanceSeconds !== null && $balanceSeconds < 0,
            'overdue_seconds' => ($balanceSeconds !== null && $balanceSeconds < 0)
                ? abs($balanceSeconds)
                : null,
        ];
    }

    public static function submitToReview(User $actor, Task $task, Request $request): Task
    {
        abort_unless((string) $task->id_user === (string) $actor->id, 403);

        $task->loadMissing('status');

        $oldProgress = TaskRunningTimer::isInProgressStatus($task->status);
        $oldRevision = TaskRunningTimer::isRevisionStatus($task->status);

        if (! $oldProgress && ! $oldRevision) {
            throw ValidationException::withMessages([
                'task' => 'Task harus berstatus In Progress atau Revision untuk dikirim ke Review.',
            ]);
        }

        $validated = $request->validate([
            'notes' => 'required|string|max:5000',
            'links' => 'nullable|string|max:5000',
            'photos' => 'nullable|array|max:10',
            'photos.*' => 'image|mimes:jpg,jpeg|max:1024',
        ]);

        $reviewStatus = \App\Models\StatusTask::query()
            ->where('class', 'review')
            ->first();

        abort_unless($reviewStatus, 500);

        $type = $oldRevision ? TaskSubmission::TYPE_REVISION : TaskSubmission::TYPE_WORK;
        $cycleNumber = 1;

        if ($oldRevision) {
            $openCycle = TaskRevisionCycle::query()
                ->where('id_task', $task->id)
                ->whereNull('exited_revision_at')
                ->orderByDesc('cycle_number')
                ->first();

            if (! $openCycle) {
                throw ValidationException::withMessages([
                    'task' => 'Siklus revisi aktif tidak ditemukan.',
                ]);
            }

            $cycleNumber = (int) $openCycle->cycle_number;

            $exists = TaskSubmission::query()
                ->where('id_task', $task->id)
                ->where('type', TaskSubmission::TYPE_REVISION)
                ->where('cycle_number', $cycleNumber)
                ->exists();

            if ($exists) {
                throw ValidationException::withMessages([
                    'task' => 'Hasil revisi untuk siklus ini sudah dikirim.',
                ]);
            }
        } else {
            $exists = TaskSubmission::query()
                ->where('id_task', $task->id)
                ->where('type', TaskSubmission::TYPE_WORK)
                ->exists();

            if ($exists) {
                throw ValidationException::withMessages([
                    'task' => 'Hasil kerja awal sudah pernah dikirim.',
                ]);
            }
        }

        $links = self::normalizeLinks($validated['links'] ?? null);

        return DB::transaction(function () use ($actor, $task, $request, $validated, $reviewStatus, $type, $cycleNumber, $links, $oldRevision, $oldProgress) {
            $submission = TaskSubmission::create([
                'id_task' => $task->id,
                'type' => $type,
                'cycle_number' => $cycleNumber,
                'notes' => trim($validated['notes']),
                'links' => $links,
                'submitted_by' => $actor->id,
            ]);

            TaskPhotoManager::storeFromRequest($request, $task, $actor, $submission->id, null);

            if ($oldRevision) {
                TaskRevisionCycle::query()
                    ->where('id_task', $task->id)
                    ->whereNull('exited_revision_at')
                    ->latest('entered_revision_at')
                    ->limit(1)
                    ->update(['exited_revision_at' => now()]);
            }

            $task->id_status = $reviewStatus->id;

            if ($oldProgress && ! $task->running_review_at) {
                $task->running_review_at = now();
            }

            $task->save();
            $task->refresh()->load(['status', 'difficulty', 'user']);

            if ($task->user) {
                StatusSdmManager::syncForUser($task->user);
            }

            TaskAuditLogger::info('task_submit_review', [
                'result' => 'success',
                'actor_id' => $actor->id,
                'task_id' => $task->id,
                'submission_id' => $submission->id,
                'submission_type' => $type,
                'cycle_number' => $cycleNumber,
            ]);

            return $task;
        });
    }

    public static function normalizeLinks(?string $raw): ?string
    {
        if ($raw === null) {
            return null;
        }

        $trimmed = trim($raw);

        return $trimmed !== '' ? $trimmed : null;
    }

    /**
     * @return list<array{id: string, url: string, original_name: ?string}>
     */
    private static function mapPhotos($photos): array
    {
        return $photos->map(fn ($p) => [
            'id' => $p->id,
            'url' => $p->url,
            'original_name' => $p->original_name,
        ])->values()->all();
    }

    /**
     * @return array<string, mixed>
     */
    private static function mapSubmission(TaskSubmission $submission): array
    {
        $submission->loadMissing(['photos', 'submitter:id,name']);

        return [
            'id' => $submission->id,
            'type' => $submission->type,
            'cycle_number' => (int) $submission->cycle_number,
            'notes' => $submission->notes,
            'links' => $submission->links,
            'submitted_at' => $submission->created_at?->toIso8601String(),
            'submitter' => $submission->submitter ? [
                'id' => $submission->submitter->id,
                'name' => $submission->submitter->name,
            ] : null,
            'photos' => self::mapPhotos($submission->photos),
        ];
    }
}
