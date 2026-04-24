<?php

namespace App\Http\Controllers\Director\Tasks;

use App\Http\Controllers\Controller;
use App\Models\StatusTask;
use App\Models\Task;
use App\Support\StatusSdmManager;
use App\Support\TaskBoardAccess;
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
        abort_unless(Auth::user()->role?->role === 'director', 403);

        $task = Task::with(['status', 'project', 'user.role'])->findOrFail($id);

        TaskBoardAccess::assertCanActOnTaskForBoard(Auth::user(), $task);
        abort_unless(($task->user?->role?->role ?? null) === 'staff', 403);
        abort_unless((string) ($task->project?->id_director ?? '') === (string) Auth::id(), 403);

        abort_unless(TaskRunningTimer::isReviewStatus($task->status), 422);

        $validated = $request->validate([
            'decision' => 'required|in:complete,revision',
            'revision_hours' => 'required_if:decision,revision|nullable|in:2,3,4',
        ]);

        $completeStatus = StatusTask::query()->where('class', 'complete')->first()
            ?? StatusTask::query()->where('status', 'Complete')->first();
        $revisionStatus = StatusTask::query()->where('class', 'revision')->first();

        if ($validated['decision'] === 'complete') {
            abort_unless($completeStatus, 500);
            $task->id_status = $completeStatus->id;
            $task->running_started_at = null;
            $task->running_review_at = null;
            $task->revision_deadline_at = null;
            $task->revision_hours = null;
        } else {
            abort_unless($revisionStatus, 500);
            $hours = (int) $validated['revision_hours'];
            $task->id_status = $revisionStatus->id;
            $task->running_started_at = null;
            $task->running_review_at = null;
            $task->revision_hours = $hours;
            $task->revision_deadline_at = now()->addHours($hours);
        }

        $task->save();

        if ($task->user) {
            StatusSdmManager::syncForUser($task->user);
        }

        return redirect()->back()->with('success', 'Status review berhasil diperbarui.');
    }
}
