<?php

namespace App\Support;

use App\Models\Administration;
use App\Models\Project;
use App\Models\Task;
use App\Models\TaskOwnershipTransfer;
use App\Models\TaskOwnershipTransferRequest;
use App\Models\TaskRevisionCycle;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TaskOwnershipManager
{
  /** @return list<string> */
    public static function transferableStatusClasses(): array
    {
        return ['todo', 'progress', 'revision'];
    }

    public static function isTransferable(Task $task): bool
    {
        $task->loadMissing('status');

        $class = $task->status?->class ?? '';
        if (in_array($class, self::transferableStatusClasses(), true)) {
            return true;
        }

        $label = strtolower((string) ($task->status?->status ?? ''));

        return in_array($label, ['to do', 'in progress', 'revision'], true);
    }

    public static function isUserAbsentToday(User $user): bool
    {
        $today = now()->toDateString();

        return Administration::query()
            ->where('id_user', $user->id)
            ->whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today)
            ->whereHas('status', function ($q) {
                $q->where('name', 'accept');
            })
            ->exists();
    }

    /**
     * @return Collection<int, User>
     */
    public static function eligibleRecipients(Task $task): Collection
    {
        $task->loadMissing(['project.sdms', 'user']);
        $project = $task->project;
        if (! $project) {
            return collect();
        }

        $ownerId = (string) $task->id_user;

        return $project->sdms
            ->filter(function (User $sdm) use ($ownerId) {
                if ((string) $sdm->id === $ownerId) {
                    return false;
                }

                return ! self::isUserAbsentToday($sdm);
            })
            ->values();
    }

    public static function assertRecipientEligible(Task $task, string $toUserId): void
    {
        $eligibleIds = self::eligibleRecipients($task)->pluck('id')->map(fn ($id) => (string) $id);

        if (! $eligibleIds->contains((string) $toUserId)) {
            throw ValidationException::withMessages([
                'to_user_id' => 'Penerima tidak valid: harus anggota tim project dan tidak sedang Absent.',
            ]);
        }
    }

    public static function assertNoPendingRequest(Task $task): void
    {
        $exists = TaskOwnershipTransferRequest::query()
            ->where('id_task', $task->id)
            ->where('status', TaskOwnershipTransferRequest::STATUS_PENDING)
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'task' => 'Sudah ada pengajuan alih kepemilikan yang menunggu persetujuan.',
            ]);
        }
    }

    public static function submitRequest(User $actor, Task $task, string $toUserId, string $reason): TaskOwnershipTransferRequest
    {
        abort_unless(TaskBoardAccess::canRequestOwnershipTransfer($actor, $task), 403);

        if (! self::isTransferable($task)) {
            throw ValidationException::withMessages([
                'task' => 'Task dengan status ini tidak dapat dialihkan.',
            ]);
        }

        self::assertNoPendingRequest($task);
        self::assertRecipientEligible($task, $toUserId);

        $reason = trim($reason);
        if ($reason === '') {
            throw ValidationException::withMessages([
                'reason' => 'Alasan pengajuan wajib diisi.',
            ]);
        }

        $request = TaskOwnershipTransferRequest::create([
            'id_task' => $task->id,
            'requested_by' => $actor->id,
            'from_user_id' => $task->id_user,
            'to_user_id' => $toUserId,
            'reason' => $reason,
            'status' => TaskOwnershipTransferRequest::STATUS_PENDING,
        ]);

        TaskAuditLogger::info('task_ownership_request', [
            'result' => 'pending',
            'actor_id' => $actor->id,
            'task_id' => $task->id,
            'from_user_id' => $task->id_user,
            'to_user_id' => $toUserId,
            'request_id' => $request->id,
        ]);

        return $request;
    }

    public static function approveRequest(
        User $director,
        TaskOwnershipTransferRequest $request,
        ?string $toUserIdOverride = null,
        ?string $reviewNote = null
    ): void {
        $request->loadMissing('task.project');
        $task = $request->task;

        abort_unless($task, 404);
        abort_unless(TaskBoardAccess::canReviewOwnershipRequest($director, $task), 403);

        if ($request->status !== TaskOwnershipTransferRequest::STATUS_PENDING) {
            throw ValidationException::withMessages([
                'request' => 'Pengajuan ini sudah diproses.',
            ]);
        }

        if (! self::isTransferable($task)) {
            throw ValidationException::withMessages([
                'task' => 'Task dengan status ini tidak dapat dialihkan.',
            ]);
        }

        $toUserId = $toUserIdOverride ?: $request->to_user_id;
        self::assertRecipientEligible($task, $toUserId);

        DB::transaction(function () use ($director, $request, $task, $toUserId, $reviewNote) {
            self::executeTransfer(
                $task,
                (string) $request->from_user_id,
                (string) $toUserId,
                (string) $director->id,
                $request->reason,
                'request_approved',
                $request->id
            );

            $request->update([
                'status' => TaskOwnershipTransferRequest::STATUS_APPROVED,
                'to_user_id' => $toUserId,
                'reviewed_by' => $director->id,
                'review_note' => $reviewNote ? trim($reviewNote) : null,
                'reviewed_at' => now(),
            ]);
        });

        TaskAuditLogger::info('task_ownership_request', [
            'result' => 'approved',
            'actor_id' => $director->id,
            'task_id' => $task->id,
            'request_id' => $request->id,
            'to_user_id' => $toUserId,
        ]);
    }

    public static function rejectRequest(
        User $director,
        TaskOwnershipTransferRequest $request,
        ?string $reviewNote = null
    ): void {
        $request->loadMissing('task.project');
        $task = $request->task;

        abort_unless($task, 404);
        abort_unless(TaskBoardAccess::canReviewOwnershipRequest($director, $task), 403);

        if ($request->status !== TaskOwnershipTransferRequest::STATUS_PENDING) {
            throw ValidationException::withMessages([
                'request' => 'Pengajuan ini sudah diproses.',
            ]);
        }

        $request->update([
            'status' => TaskOwnershipTransferRequest::STATUS_REJECTED,
            'reviewed_by' => $director->id,
            'review_note' => $reviewNote ? trim($reviewNote) : null,
            'reviewed_at' => now(),
        ]);

        TaskAuditLogger::info('task_ownership_request', [
            'result' => 'rejected',
            'actor_id' => $director->id,
            'task_id' => $task->id,
            'request_id' => $request->id,
        ]);
    }

    public static function directReassign(User $director, Task $task, string $toUserId, ?string $reason = null): void
    {
        abort_unless(TaskBoardAccess::canDirectReassignOwnership($director, $task), 403);

        if (! self::isTransferable($task)) {
            throw ValidationException::withMessages([
                'task' => 'Task dengan status ini tidak dapat dialihkan.',
            ]);
        }

        self::assertNoPendingRequest($task);
        self::assertRecipientEligible($task, $toUserId);

        $fromUserId = (string) $task->id_user;

        DB::transaction(function () use ($director, $task, $fromUserId, $toUserId, $reason) {
            self::executeTransfer(
                $task,
                $fromUserId,
                $toUserId,
                (string) $director->id,
                $reason ? trim($reason) : null,
                'direct_reassign',
                null
            );
        });

        TaskAuditLogger::info('task_ownership_transfer', [
            'result' => 'success',
            'source' => 'direct_reassign',
            'actor_id' => $director->id,
            'task_id' => $task->id,
            'from_user_id' => $fromUserId,
            'to_user_id' => $toUserId,
        ]);
    }

  /**
   * @return array{
   *   was_overdue_at_transfer: bool,
   *   timer_reset_at: ?\Illuminate\Support\Carbon,
   *   previous_running_started_at: ?\Illuminate\Support\Carbon,
   *   previous_revision_deadline_at: ?\Illuminate\Support\Carbon,
   *   task_updates: array<string, mixed>
   * }
   */
    private static function prepareTimerResetForTransfer(Task $task): array
    {
        $task->loadMissing(['status', 'difficulty', 'revisionCycles']);

        $previousRunningStarted = $task->running_started_at;
        $previousRevisionDeadline = $task->revision_deadline_at;
        $wasOverdue = self::wasOverdueBeforeTransfer($task);
        $now = now();
        $taskUpdates = [];

        if (TaskRunningTimer::isInProgressStatus($task->status)) {
            $taskUpdates['running_started_at'] = $now;
        } elseif (TaskRunningTimer::isRevisionStatus($task->status)) {
            $hours = (int) ($task->revision_hours ?? 0);
            if ($hours > 0) {
                $newDeadline = $now->copy()->addHours($hours);
                $taskUpdates['revision_deadline_at'] = $newDeadline;

                $openCycle = $task->revisionCycles
                    ->whereNull('exited_revision_at')
                    ->sortByDesc('cycle_number')
                    ->first();

                if ($openCycle) {
                    TaskRevisionCycle::query()
                        ->where('id', $openCycle->id)
                        ->update([
                            'entered_revision_at' => $now,
                            'deadline_at' => $newDeadline,
                        ]);
                }
            }
        }

        $timerResetAt = $taskUpdates !== [] ? $now : null;

        return [
            'was_overdue_at_transfer' => $wasOverdue,
            'timer_reset_at' => $timerResetAt,
            'previous_running_started_at' => $previousRunningStarted,
            'previous_revision_deadline_at' => $previousRevisionDeadline,
            'task_updates' => $taskUpdates,
        ];
    }

    private static function wasOverdueBeforeTransfer(Task $task): bool
    {
        if (TaskRunningTimer::isRevisionStatus($task->status)) {
            if (! $task->revision_deadline_at) {
                return false;
            }

            return now()->gt($task->revision_deadline_at);
        }

        if (TaskRunningTimer::isInProgressStatus($task->status)) {
            $deadline = TaskRunningTimer::deadlineFor($task);
            if (! $deadline) {
                return false;
            }

            return now()->gt($deadline);
        }

        return false;
    }

    private static function executeTransfer(
        Task $task,
        string $fromUserId,
        string $toUserId,
        string $performedBy,
        ?string $reason,
        string $source,
        ?string $requestId
    ): void {
        $task->loadMissing('status');
        $statusLabel = $task->status?->status ?? $task->status?->class ?? null;
        $timerReset = self::prepareTimerResetForTransfer($task);

        $task->update(array_merge(
            ['id_user' => $toUserId],
            $timerReset['task_updates']
        ));

        TaskOwnershipTransfer::create([
            'id_task' => $task->id,
            'from_user_id' => $fromUserId,
            'to_user_id' => $toUserId,
            'performed_by' => $performedBy,
            'source' => $source,
            'request_id' => $requestId,
            'reason' => $reason,
            'task_status_at_transfer' => $statusLabel,
            'was_overdue_at_transfer' => $timerReset['was_overdue_at_transfer'],
            'timer_reset_at' => $timerReset['timer_reset_at'],
            'previous_running_started_at' => $timerReset['previous_running_started_at'],
            'previous_revision_deadline_at' => $timerReset['previous_revision_deadline_at'],
        ]);

        $fromUser = User::find($fromUserId);
        $toUser = User::find($toUserId);
        if ($fromUser) {
            StatusSdmManager::syncForUser($fromUser);
        }
        if ($toUser) {
            StatusSdmManager::syncForUser($toUser);
        }
    }
}
