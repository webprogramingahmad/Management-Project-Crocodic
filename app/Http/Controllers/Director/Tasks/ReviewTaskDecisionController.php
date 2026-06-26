<?php

namespace App\Http\Controllers\Director\Tasks;

use App\Http\Controllers\Controller;
use App\Models\StatusTask;
use App\Models\Task;
use App\Models\TaskRevisionCycle;
use App\Support\StatusSdmManager;
use App\Support\TaskAuditLogger;
use App\Support\TaskBoardAccess;
use App\Support\TaskPhotoManager;
use App\Support\TaskRunningTimer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewTaskDecisionController extends Controller
{
    /**
     * Director: dari Review → Complete atau Review → Revision (dengan batas jam).
     */
    public function __invoke(Request $request, string $id)
    {
        if (Auth::user()->role?->role !== 'director') {
            TaskAuditLogger::warning('task_review_decision', [
                'result' => 'forbidden',
                'reason' => 'role_not_director',
                'actor_id' => Auth::id(),
                'task_id' => $id,
            ]);
            abort(403);
        }

        $task = Task::with(['status', 'project', 'user.role'])->findOrFail($id);

        TaskBoardAccess::assertCanActOnTaskForBoard(Auth::user(), $task);
        $isOwner = (string) $task->id_user === (string) Auth::id();
        if (!$isOwner && ($task->user?->role?->role ?? null) !== 'staff') {
            TaskAuditLogger::warning('task_review_decision', [
                'result' => 'forbidden',
                'reason' => 'target_not_staff_task',
                'actor_id' => Auth::id(),
                'task_id' => $task->id,
                'task_owner_id' => $task->id_user,
            ]);
            abort(403);
        }
        if (!$isOwner && (string) ($task->project?->id_director ?? '') !== (string) Auth::id()) {
            TaskAuditLogger::warning('task_review_decision', [
                'result' => 'forbidden',
                'reason' => 'project_director_mismatch',
                'actor_id' => Auth::id(),
                'task_id' => $task->id,
                'project_id' => $task->id_project,
            ]);
            abort(403);
        }

        if (!TaskRunningTimer::isReviewStatus($task->status)) {
            TaskAuditLogger::warning('task_review_decision', [
                'result' => 'rejected',
                'reason' => 'task_not_in_review',
                'actor_id' => Auth::id(),
                'task_id' => $task->id,
                'from_status' => $task->status?->class ?? $task->status?->status,
            ]);

            return redirect()
                ->back()
                ->with('error', 'Task sudah berubah status dan tidak lagi berada di Review.');
        }

        $validated = $request->validate([
            'decision' => 'required|in:complete,revision',
            'revision_hours' => 'required_if:decision,revision|nullable|in:2,3,4',
            'revision_notes' => 'required_if:decision,revision|nullable|string|max:2000',
            'revision_links' => 'nullable|string|max:5000',
            'photos' => 'nullable|array|max:10',
            'photos.*' => 'image|mimes:jpg,jpeg|max:1024',
        ]);

        $completeStatus = StatusTask::query()->where('class', 'complete')->first()
            ?? StatusTask::firstByClass('complete');
        $revisionStatus = StatusTask::query()->where('class', 'revision')->first();

        if ($validated['decision'] === 'complete') {
            abort_unless($completeStatus, 500);
            $task->id_status = $completeStatus->id;
            $task->revision_deadline_at = null;
            $task->revision_hours = null;
        } else {
            abort_unless($revisionStatus, 500);
            $hours = (int) $validated['revision_hours'];
            $enteredAt = now();
            $deadlineAt = $enteredAt->copy()->addHours($hours);
            $task->id_status = $revisionStatus->id;
            $task->revision_hours = $hours;
            $task->revision_deadline_at = $deadlineAt;

            $nextCycle = ((int) TaskRevisionCycle::query()
                ->where('id_task', $task->id)
                ->max('cycle_number')) + 1;

            $links = \App\Support\TaskSubmissionManager::normalizeLinks($validated['revision_links'] ?? null);

            $revisionCycle = TaskRevisionCycle::query()->create([
                'id_task' => $task->id,
                'cycle_number' => $nextCycle,
                'entered_revision_at' => $enteredAt,
                'deadline_at' => $deadlineAt,
                'revision_hours' => $hours,
                'notes' => trim((string) $validated['revision_notes']),
                'links' => $links,
            ]);

            TaskPhotoManager::storeFromRequest($request, $task, Auth::user(), null, $revisionCycle->id);
        }

        $previousStatus = $task->status;
        $task->save();
        $task->refresh()->load('status');

        if ($task->user) {
            StatusSdmManager::syncForUser($task->user);
        }

        TaskAuditLogger::info('task_review_decision', [
            'result' => 'success',
            'actor_id' => Auth::id(),
            'actor_role' => 'director',
            'task_id' => $task->id,
            'project_id' => $task->id_project,
            'decision' => $validated['decision'],
            'from_status' => $previousStatus?->class ?? $previousStatus?->status,
            'to_status' => $task->status?->class ?? $task->status?->status,
            'revision_hours' => $task->revision_hours,
        ]);

        return redirect()->back()->with('success', 'Status review berhasil diperbarui.');
    }
}
