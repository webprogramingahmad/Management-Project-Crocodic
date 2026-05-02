<?php

namespace App\Http\Controllers\Admin\Tasks;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\StatusTask;
use App\Models\TaskDifficulty;
use App\Models\Task;
use App\Models\User;
use App\Support\StatusSdmManager;
use App\Support\TaskAuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TransferTaskController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'id_difficulty' => 'required|uuid|exists:task_difficulties,id',
            'id_user' => 'required|uuid|exists:users,id',
            'id_project' => ['required', 'uuid', Project::ruleExistsIdForTaskCreation()],
            'description' => 'nullable|string|max:5000',
        ]);

        $statusTodo = StatusTask::firstByClass('todo');

        $standbyDiff = TaskDifficulty::where('difficulty', 'Stand By')->first();
        if ($standbyDiff) {
            Task::where('id_user', $request->id_user)
                ->where('id_difficulty', $standbyDiff->id)
                ->delete();
        }

        $creatorId = Auth::user()->id;

        $task = Task::create([
            'name' => $request->name,
            'description' => $request->input('description') ?: null,
            'id_user' => $request->id_user,
            'id_status' => $statusTodo->id,
            'id_difficulty' => $request->id_difficulty,
            'id_project' => $request->id_project,
            'created_by' => $creatorId,
        ]);

        StatusSdmManager::syncForUser(User::findOrFail($request->id_user));
        TaskAuditLogger::info('task_transfer', [
            'result' => 'success',
            'actor_id' => $creatorId,
            'actor_role' => 'executive',
            'task_id' => $task->id,
            'project_id' => $task->id_project,
            'assignee_id' => $task->id_user,
            'to_status' => 'todo',
        ]);

        return redirect()->route('executive.tasks.index')->with('success', 'Task berhasil dibuat');
    }
}
