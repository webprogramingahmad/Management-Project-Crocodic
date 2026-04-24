<?php

namespace App\Http\Controllers\Director\Tasks;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Support\StatusSdmManager;
use App\Support\TaskBoardAccess;
use App\Support\TaskRunningTimer;

class UpdateProjectTaskController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, $id)
    {
        $project = Project::findOrFail($id);

        abort_unless($project->id_director === Auth::id(), 403);

        $task = Task::where('id_project', $project->id)->findOrFail($request->id);

        TaskBoardAccess::assertCanActOnTaskForBoard(Auth::user(), $task);
        $task->loadMissing('status');
        abort_if(TaskRunningTimer::isReviewStatus($task->status), 403);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'id_difficulty' => 'required|uuid|exists:task_difficulties,id',
            'description' => 'nullable|string|max:5000',
        ]);

        $validated['description'] = ($validated['description'] ?? '') !== ''
            ? trim((string) $validated['description'])
            : null;

        $task->update($validated);

        if ($task->user) {
            StatusSdmManager::syncForUser($task->user);
        }

        return redirect()->route('director.project.tasks.index', $project->id)->with('success', 'Task updated successfully.');
    }
}
