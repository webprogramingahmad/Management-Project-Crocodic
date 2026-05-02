<?php

namespace App\Http\Controllers;

use App\Models\StatusTask;
use App\Models\Task;
use App\Models\TaskRevisionCycle;
use App\Support\StatusSdmManager;
use App\Support\TaskAuditLogger;
use App\Support\TaskBoardAccess;
use App\Support\TaskRunningTimer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UpdateStatusTaskController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, $id)
    {
        $request->validate([
            'id_status' => 'required|uuid|exists:status_tasks,id',
        ]);

        $actor = Auth::user();
        $actor->loadMissing('role');
        $actorRole = $actor->role?->role;
        if ($actorRole === 'executive') {
            TaskAuditLogger::warning('task_update_status', [
                'result' => 'forbidden',
                'reason' => 'executive_monitor_only',
                'actor_id' => $actor->id,
                'actor_role' => $actorRole,
                'task_id' => $id,
            ]);
            abort(403);
        }

        $task = Task::with(['status', 'user.role'])->findOrFail($id);

        TaskBoardAccess::assertCanActOnTaskForBoard($actor, $task);

        $incoming = StatusTask::findOrFail($request->id_status);

        $oldStatus = $task->status;
        $oldTodo = (($oldStatus?->class ?? '') === 'todo')
            || strcasecmp((string) ($oldStatus?->status ?? ''), 'To Do') === 0;
        $oldProgress = TaskRunningTimer::isInProgressStatus($oldStatus);
        $oldReview = TaskRunningTimer::isReviewStatus($oldStatus);
        $oldRevision = TaskRunningTimer::isRevisionStatus($oldStatus);
        $oldComplete = TaskRunningTimer::isCompleteStatus($oldStatus);
        $incomingTodo = (($incoming->class ?? '') === 'todo')
            || strcasecmp((string) ($incoming->status ?? ''), 'To Do') === 0;
        $newProgress = TaskRunningTimer::isInProgressStatus($incoming);
        $newReview = TaskRunningTimer::isReviewStatus($incoming);
        $newRevision = TaskRunningTimer::isRevisionStatus($incoming);
        $incomingComplete = TaskRunningTimer::isCompleteStatus($incoming);

        $isOwner = (string) $task->id_user === (string) $actor->id;
        // Staff hanya boleh ubah status task miliknya sendiri.
        if ($actorRole === 'staff' && ! $isOwner) {
            TaskAuditLogger::warning('task_update_status', [
                'result' => 'forbidden',
                'reason' => 'staff_non_owner',
                'actor_id' => $actor->id,
                'actor_role' => $actorRole,
                'task_id' => $task->id,
                'task_owner_id' => $task->id_user,
            ]);
            abort(403);
        }

        // Director hanya boleh ubah status task miliknya sendiri via endpoint ini.
        // Perubahan task staff oleh director dilakukan via endpoint review decision khusus.
        if ($actorRole === 'director' && ! $isOwner) {
            TaskAuditLogger::warning('task_update_status', [
                'result' => 'forbidden',
                'reason' => 'director_non_owner',
                'actor_id' => $actor->id,
                'actor_role' => $actorRole,
                'task_id' => $task->id,
                'task_owner_id' => $task->id_user,
            ]);
            abort(403);
        }

        // Revision hanya lewat keputusan review (bukan drag).
        if ($newRevision) {
            TaskAuditLogger::warning('task_update_status', [
                'result' => 'forbidden',
                'reason' => 'revision_only_via_review_decision',
                'actor_id' => $actor->id,
                'actor_role' => $actorRole,
                'task_id' => $task->id,
            ]);
            abort(403);
        }

        // Staff tidak boleh memindahkan task ke Complete.
        if ($actorRole === 'staff' && $incomingComplete) {
            TaskAuditLogger::warning('task_update_status', [
                'result' => 'forbidden',
                'reason' => 'staff_cannot_complete',
                'actor_id' => $actor->id,
                'actor_role' => $actorRole,
                'task_id' => $task->id,
            ]);
            abort(403);
        }

        // Review → Complete hanya boleh untuk director pada task miliknya sendiri.
        if ($oldReview && $incomingComplete) {
            if (! ($actorRole === 'director' && $isOwner)) {
                TaskAuditLogger::warning('task_update_status', [
                    'result' => 'forbidden',
                    'reason' => 'review_to_complete_requires_director_owner',
                    'actor_id' => $actor->id,
                    'actor_role' => $actorRole,
                    'task_id' => $task->id,
                ]);
                abort(403);
            }
        }

        // In progress tidak boleh kembali ke To Do.
        if ($oldProgress && $incomingTodo) {
            TaskAuditLogger::warning('task_update_status', [
                'result' => 'forbidden',
                'reason' => 'progress_cannot_return_todo',
                'actor_id' => $actor->id,
                'actor_role' => $actorRole,
                'task_id' => $task->id,
            ]);
            abort(403);
        }

        // Review tidak boleh kembali ke In progress atau To Do.
        if ($oldReview && ($newProgress || $incomingTodo)) {
            TaskAuditLogger::warning('task_update_status', [
                'result' => 'forbidden',
                'reason' => 'review_cannot_return_progress_or_todo',
                'actor_id' => $actor->id,
                'actor_role' => $actorRole,
                'task_id' => $task->id,
            ]);
            abort(403);
        }

        // Complete bersifat final (tidak boleh mundur ke status lain).
        if ($oldComplete && ! $incomingComplete) {
            TaskAuditLogger::warning('task_update_status', [
                'result' => 'forbidden',
                'reason' => 'complete_is_final',
                'actor_id' => $actor->id,
                'actor_role' => $actorRole,
                'task_id' => $task->id,
            ]);
            abort(403);
        }

        // Revision -> Review hanya untuk pemilik task itu sendiri.
        if ($oldRevision && $newReview && ! $isOwner) {
            TaskAuditLogger::warning('task_update_status', [
                'result' => 'forbidden',
                'reason' => 'revision_to_review_non_owner',
                'actor_id' => $actor->id,
                'actor_role' => $actorRole,
                'task_id' => $task->id,
            ]);
            abort(403);
        }

        if ($oldRevision && $newReview) {
            TaskRevisionCycle::query()
                ->where('id_task', $task->id)
                ->whereNull('exited_revision_at')
                ->latest('entered_revision_at')
                ->limit(1)
                ->update(['exited_revision_at' => now()]);
        }

        $task->id_status = $incoming->id;

        if ($newProgress) {
            if (!$task->running_started_at) {
                $task->running_started_at = now();
            }
            $task->revision_deadline_at = null;
            $task->revision_hours = null;
        } elseif ($newReview && $oldProgress) {
            if (!$task->running_review_at) {
                $task->running_review_at = now();
            }
        } elseif (!$newProgress && !$newReview && !$newRevision) {
            $task->revision_deadline_at = null;
            $task->revision_hours = null;
        }

        $task->save();

        $task->refresh()->load(['status', 'difficulty', 'user']);

        if ($task->user) {
            StatusSdmManager::syncForUser($task->user);
        }

        $frozenMs = TaskRunningTimer::frozenRemainMsForReview($task);
        TaskAuditLogger::info('task_update_status', [
            'result' => 'success',
            'actor_id' => $actor->id,
            'actor_role' => $actorRole,
            'task_id' => $task->id,
            'project_id' => $task->id_project,
            'from_status' => $oldStatus?->class ?? $oldStatus?->status,
            'to_status' => $incoming->class ?? $incoming->status,
        ]);

        return response()->json([
            'success' => true,
            'task' => $task,
            'deadline_iso' => TaskRunningTimer::shouldShowLiveTimer($task) ? TaskRunningTimer::deadlineIsoString($task) : null,
            'show_timer' => TaskRunningTimer::shouldShowTimer($task),
            'frozen_remain_ms' => $frozenMs,
            'progress_balance_seconds' => TaskRunningTimer::progressBalanceSeconds($task),
            'revision_cycles' => TaskRunningTimer::revisionCycleBalances($task),
        ]);
    }
}
