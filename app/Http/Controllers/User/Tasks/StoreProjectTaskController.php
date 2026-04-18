<?php

namespace App\Http\Controllers\User\Tasks;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\StatusTask;
use App\Models\TaskDifficulty;
use App\Models\Task;
use App\Models\User;
use App\Support\StatusSdmManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StoreProjectTaskController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, $id)
    {
        $project = Project::with('status')->findOrFail($id);

        abort_unless($project->sdms()->where('users.id', Auth::id())->exists(), 403);

        if (! $project->allowsTaskCreation()) {
            return redirect()->back()->withErrors([
                'project' => 'Task hanya dapat dibuat jika project berstatus Running atau Maintenance.',
            ]);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'id_difficulty' => 'required|uuid|exists:task_difficulties,id',
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
            'id_user' => $userId,
            'id_status' => $statusTodo->id,
            'id_difficulty' => $request->id_difficulty,
            'id_project' => $project->id,
            'created_by' => $userId,
        ]);

        StatusSdmManager::syncForUser(User::findOrFail($userId));

        return redirect()->route('staff.project.tasks.index', $project->id)->with('success', 'Task berhasil dibuat');
    }
}
