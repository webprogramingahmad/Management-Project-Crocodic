<?php

namespace App\Http\Controllers\Admin\Tasks;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\StatusTask;
use App\Models\Task;
use App\Models\TaskDifficulty;
use App\Support\StatusSdmManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StoreTaskController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'id_difficulty' => 'required|uuid|exists:task_difficulties,id',
            'id_project' => ['required', 'uuid', Project::ruleExistsIdForTaskCreation()],
            'description' => 'nullable|string|max:5000',
        ]);

        $statusTodo = StatusTask::where('status', 'To Do')->first();

        $userId = Auth::user()->id;

        $standbyDiff = TaskDifficulty::where('difficulty', 'Stand By')->first();
        if ($standbyDiff) {
            Task::where('id_user', $userId)
                ->where('id_difficulty', $standbyDiff->id)
                ->delete();
        }

        Task::create([
            'name' => $request->name,
            'description' => $request->input('description') ?: null,
            'id_difficulty' => $request->id_difficulty,
            'id_project' => $request->id_project,
            'id_status' => $statusTodo->id,
            'id_user' => $userId,
            'created_by' => $userId,
        ]);

        StatusSdmManager::syncForUser(Auth::user());

        return redirect()->route('executive.tasks.index')->with('success', 'Task berhasil dibuat');
    }
}
