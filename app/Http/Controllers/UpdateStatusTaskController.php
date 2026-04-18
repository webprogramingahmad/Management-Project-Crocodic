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

        $task = Task::findOrFail($id);

        TaskBoardAccess::assertCanActOnTaskForBoard(Auth::user(), $task);

        $incoming = StatusTask::findOrFail($request->id_status);

        $task->id_status = $incoming->id;

        if (TaskRunningTimer::isInProgressStatus($incoming)) {
            $task->running_started_at = now();
        } else {
            $task->running_started_at = null;
        }

        $task->save();

        $task->refresh()->load(['status', 'difficulty', 'user']);

        if ($task->user) {
            StatusSdmManager::syncForUser($task->user);
        }

        return response()->json([
            'success' => true,
            'task' => $task,
            'deadline_iso' => TaskRunningTimer::deadlineIsoString($task),
            'show_timer' => TaskRunningTimer::shouldShowTimer($task),
        ]);
    }
}
