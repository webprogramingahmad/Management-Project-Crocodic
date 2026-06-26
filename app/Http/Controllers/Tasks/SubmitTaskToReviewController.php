<?php

namespace App\Http\Controllers\Tasks;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Support\TaskBoardAccess;
use App\Support\TaskRunningTimer;
use App\Support\TaskSubmissionManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SubmitTaskToReviewController extends Controller
{
    public function __invoke(Request $request, string $id)
    {
        $actor = Auth::user();
        $actor->loadMissing('role');

        if ($actor->role?->role === 'executive') {
            abort(403);
        }

        $task = Task::with(['status', 'user.role'])->findOrFail($id);

        TaskBoardAccess::assertCanActOnTaskForBoard($actor, $task);

        $task = TaskSubmissionManager::submitToReview($actor, $task, $request);

        $frozenMs = TaskRunningTimer::frozenRemainMsForReview($task);

        return response()->json([
            'success' => true,
            'task' => $task,
            'has_submissions' => true,
            'deadline_iso' => TaskRunningTimer::shouldShowLiveTimer($task) ? TaskRunningTimer::deadlineIsoString($task) : null,
            'show_timer' => TaskRunningTimer::shouldShowTimer($task),
            'frozen_remain_ms' => $frozenMs,
            'progress_balance_seconds' => TaskRunningTimer::progressBalanceSeconds($task),
            'revision_cycles' => TaskRunningTimer::revisionCycleBalances($task),
        ]);
    }
}
