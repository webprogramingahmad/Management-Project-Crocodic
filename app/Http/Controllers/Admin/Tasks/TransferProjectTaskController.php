<?php

namespace App\Http\Controllers\Admin\Tasks;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\StatusTask;
use App\Models\TaskDifficulty;
use App\Models\Task;
use App\Models\User;
use App\Support\StatusSdmManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TransferProjectTaskController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, $id)
    {
        $project = Project::with('status')->findOrFail($id);

        if (! $project->allowsTaskCreation()) {
            return redirect()->back()->withErrors([
                'project' => 'Task hanya dapat dibuat jika project berstatus Running atau Maintenance.',
            ]);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'id_difficulty' => 'required|uuid|exists:task_difficulties,id',
            'id_user' => 'required|uuid|exists:users,id',
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

        Task::create([
            'name' => $request->name,
            'description' => $request->input('description') ?: null,
            'id_user' => $request->id_user,
            'id_status' => $statusTodo->id,
            'id_difficulty' => $request->id_difficulty,
            'id_project' => $project->id,
            'created_by' => $creatorId,
        ]);

        StatusSdmManager::syncForUser(User::findOrFail($request->id_user));

        return redirect()->route('executive.project.tasks.index', $project->id)->with('success', 'Task berhasil dibuat');
    }
}
