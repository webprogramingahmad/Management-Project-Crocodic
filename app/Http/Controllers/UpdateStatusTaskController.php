<?php

namespace App\Http\Controllers;

use App\Models\StatusTask;
use App\Models\Task;
use App\Support\StatusSdmManager;
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
        abort_if($actor->role?->role === 'executive', 403);

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

        // Director pada task milik staff: hanya boleh putuskan Review -> Revision/Complete via form khusus.
        $isOwner = (string) $task->id_user === (string) $actor->id;
        if ($actor->role?->role === 'director' && ! $isOwner) {
            abort_if(($task->user?->role?->role ?? null) !== 'staff', 403);
            abort_if(! $oldReview || (! $newRevision && ! $incomingComplete), 403);
        }

        // Revision hanya lewat keputusan review (bukan drag).
        abort_if($newRevision, 403);

        // Review → Complete hanya lewat form director (bukan drag).
        if ($oldReview) {
            abort_if($incomingComplete, 403);
        }

        // In progress tidak boleh kembali ke To Do.
        if ($oldProgress && $incomingTodo) {
            abort(403);
        }

        // Review tidak boleh kembali ke In progress atau To Do.
        if ($oldReview && ($newProgress || $incomingTodo)) {
            abort(403);
        }

        // Complete bersifat final (tidak boleh mundur ke status lain).
        if ($oldComplete && ! $incomingComplete) {
            abort(403);
        }

        // Revision -> Review hanya untuk pemilik task itu sendiri.
        if ($oldRevision && $newReview && ! $isOwner) {
            abort(403);
        }

        $task->id_status = $incoming->id;

        if ($newProgress) {
            $task->running_started_at = now();
            $task->running_review_at = null;
            $task->revision_deadline_at = null;
            $task->revision_hours = null;
        } elseif ($newReview && ($oldProgress || $oldRevision)) {
            $task->running_review_at = now();
        } elseif (!$newProgress && !$newReview && !$newRevision) {
            $task->running_started_at = null;
            $task->running_review_at = null;
            $task->revision_deadline_at = null;
            $task->revision_hours = null;
        }

        $task->save();

        $task->refresh()->load(['status', 'difficulty', 'user']);

        if ($task->user) {
            StatusSdmManager::syncForUser($task->user);
        }

        $frozenMs = TaskRunningTimer::frozenRemainMsForReview($task);

        return response()->json([
            'success' => true,
            'task' => $task,
            'deadline_iso' => TaskRunningTimer::shouldShowLiveTimer($task) ? TaskRunningTimer::deadlineIsoString($task) : null,
            'show_timer' => TaskRunningTimer::shouldShowTimer($task),
            'frozen_remain_ms' => $frozenMs,
        ]);
    }
}
