<?php

namespace App\Http\Controllers\Director\Tasks;

use App\Http\Controllers\Controller;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Support\StatusSdmManager;
use App\Support\TaskBoardAccess;
use App\Support\TaskPhotoManager;
use App\Support\TaskRunningTimer;

class UpdateTaskController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $task = Task::findOrFail($request->id);

        TaskBoardAccess::assertCanActOnTaskForBoard(Auth::user(), $task);

        $task->loadMissing('status');
        abort_if(TaskRunningTimer::isReviewStatus($task->status), 403);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'id_difficulty' => 'required|uuid|exists:task_difficulties,id',
            'description' => 'nullable|string|max:5000',
            'photos' => 'nullable|array|max:10',
            'photos.*' => 'image|mimes:jpg,jpeg|max:1024',
        ]);

        $validated['description'] = ($validated['description'] ?? '') !== ''
            ? trim((string) $validated['description'])
            : null;
        unset($validated['photos']);

        $task->update($validated);

        TaskPhotoManager::storeFromRequest($request, $task, Auth::user());

        if ($task->user) {
            StatusSdmManager::syncForUser($task->user);
        }

        return redirect()->route('director.tasks.index')->with('success', 'Task updated successfully.');
    }
}
