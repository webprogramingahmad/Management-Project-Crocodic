<?php

namespace App\Http\Controllers\Tasks;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Support\TaskBoardAccess;
use App\Support\TaskSubmissionManager;
use Illuminate\Support\Facades\Auth;

class ShowTaskSubmissionsController extends Controller
{
    public function __invoke(string $id)
    {
        $task = Task::with(['project', 'user'])->findOrFail($id);

        TaskBoardAccess::assertCanActOnTaskForBoard(Auth::user(), $task);

        return response()->json([
            'success' => true,
            'task_id' => $task->id,
            'task_name' => $task->name,
            'data' => TaskSubmissionManager::buildPayloadForTask($task),
        ]);
    }
}
