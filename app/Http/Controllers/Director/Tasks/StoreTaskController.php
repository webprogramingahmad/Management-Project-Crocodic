<?php

namespace App\Http\Controllers\Director\Tasks;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\StatusTask;
use App\Models\Task;
use App\Support\StatusSdmManager;
use App\Support\TaskAuditLogger;
use App\Support\TaskBoardAccess;
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
            'description' => 'nullable|string|max:5000',
        ]);

        $isStandby = $request->id_difficulty === 'standby';

        $createdTask = null;

        if ($isStandby) {
            $statusTodo = StatusTask::firstByClass('todo');
            $user = Auth::user();
            $standbyDiff = \App\Models\TaskDifficulty::where('difficulty', 'Stand By')->first();
            if (!$standbyDiff) {
                $standbyDiff = \App\Models\TaskDifficulty::create([
                    'difficulty' => 'Stand By',
                    'class' => 'bg-secondary',
                ]);
            } elseif (empty($standbyDiff->class)) {
                $standbyDiff->class = 'bg-secondary';
                $standbyDiff->save();
            }
            Task::where('id_user', $user->id)
                ->where('id_difficulty', $standbyDiff->id)
                ->delete();
            $createdTask = Task::create([
                'name' => 'Stand By',
                'description' => $request->filled('description') ? trim((string) $request->input('description')) : null,
                'id_difficulty' => $standbyDiff->id,
                'id_project' => null,
                'id_status' => $statusTodo->id,
                'id_user' => $user->id,
                'created_by' => $user->id,
            ]);
        } else {
            $request->validate([
                'name' => 'required|string|max:255',
                'id_difficulty' => 'required|uuid|exists:task_difficulties,id',
                'id_project' => ['required', 'uuid', Project::ruleExistsIdForTaskCreation()],
                'description' => 'required|string|max:5000',
            ]);

            TaskBoardAccess::assertCanUseProjectForTaskMutation(Auth::user(), (string) $request->id_project);

            $statusTodo = StatusTask::firstByClass('todo');
            $userId = Auth::user()->id;
            $standbyDiff = \App\Models\TaskDifficulty::where('difficulty', 'Stand By')->first();
            if ($standbyDiff) {
                Task::where('id_user', $userId)
                    ->where('id_difficulty', $standbyDiff->id)
                    ->delete();
            }
            $createdTask = Task::create([
                'name' => $request->name,
                'description' => trim((string) $request->input('description')),
                'id_difficulty' => $request->id_difficulty,
                'id_project' => $request->id_project,
                'id_status' => $statusTodo->id,
                'id_user' => $userId,
                'created_by' => $userId,
            ]);
        }

        StatusSdmManager::syncForUser(Auth::user());
        if ($createdTask) {
            TaskAuditLogger::info('task_create', [
                'result' => 'success',
                'actor_id' => Auth::id(),
                'actor_role' => 'director',
                'task_id' => $createdTask->id,
                'project_id' => $createdTask->id_project,
                'to_status' => 'todo',
                'is_standby' => $isStandby,
            ]);
        }

        return redirect()->route('director.tasks.index')->with('success', 'Task berhasil dibuat');
    }
}
