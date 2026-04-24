<?php

namespace App\Http\Controllers\User\Tasks;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\StatusTask;
use App\Models\Task;
use App\Models\TaskDifficulty;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IndexProjectTaskController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, $id)
    {
        $project = Project::with(['sdms', 'status'])->findOrFail($id);

        abort_unless($project->sdms()->where('users.id', Auth::id())->exists(), 403);

        $projectAllowsTaskCreation = $project->allowsTaskCreation();
        $tasks = Task::with(['user', 'difficulty', 'status', 'project'])
            ->where('id_project', $id)
            ->excludingStandByDifficulty()
            ->get();
        $projects = Project::whereHas('sdms', function ($q) {
            $q->where('users.id', Auth::id());
        })->get();
        $date      = $request->input('date');

        $taskTodo = Task::with(['user', 'difficulty', 'status', 'project'])
            ->where('tasks.id_project', $project->id)
            ->excludingStandByDifficulty()
            ->join('status_tasks', 'status_tasks.id', '=', 'tasks.id_status')
            ->where('status_tasks.status', 'To Do')
            ->when($date, fn($q) => $q->whereDate('tasks.created_at', $date))
            ->select('tasks.*')
            ->get();

        $taskProgress = Task::with(['user', 'difficulty', 'status', 'project'])
            ->where('tasks.id_project', $project->id)
            ->excludingStandByDifficulty()
            ->join('status_tasks', 'status_tasks.id', '=', 'tasks.id_status')
            ->where('status_tasks.status', 'In progress')
            ->when($date, fn($q) => $q->whereDate('tasks.created_at', $date))
            ->select('tasks.*')
            ->get();

        $taskReview = Task::with(['user', 'difficulty', 'status', 'project'])
            ->where('tasks.id_project', $project->id)
            ->excludingStandByDifficulty()
            ->join('status_tasks', 'status_tasks.id', '=', 'tasks.id_status')
            ->where('status_tasks.status', 'Review')
            ->when($date, fn($q) => $q->whereDate('tasks.created_at', $date))
            ->select('tasks.*')
            ->get();

        $taskRevision = Task::with(['user', 'difficulty', 'status', 'project'])
            ->where('tasks.id_project', $project->id)
            ->excludingStandByDifficulty()
            ->join('status_tasks', 'status_tasks.id', '=', 'tasks.id_status')
            ->where('status_tasks.status', 'Revision')
            ->when($date, fn($q) => $q->whereDate('tasks.created_at', $date))
            ->select('tasks.*')
            ->get();

        $taskComplete = Task::with(['user', 'difficulty', 'status', 'project'])
            ->where('tasks.id_project', $project->id)
            ->excludingStandByDifficulty()
            ->join('status_tasks', 'status_tasks.id', '=', 'tasks.id_status')
            ->where('status_tasks.status', 'Complete')
            ->when(
                $date,
                fn($q) =>
                $q->whereDate('tasks.updated_at', $date),
                fn($q) =>
                $q->whereDate('tasks.updated_at', now()->toDateString())
            )
            ->select('tasks.*')
            ->get();

        $difficulties = TaskDifficulty::oldest()
            ->where('difficulty', '!=', 'Stand By')
            ->get();


        $statusTodo     = StatusTask::where('status', 'To Do')->first();
        $statusProgress = StatusTask::where('status', 'In progress')->first();
        $statusReview   = StatusTask::where('status', 'Review')->first();
        $statusRevision = StatusTask::where('status', 'Revision')->first();
        $statusComplete = StatusTask::where('status', 'Complete')->first();

        return view('view.tasks.index-project', compact('tasks', 'project', 'projects', 'projectAllowsTaskCreation', 'difficulties', 'taskTodo', 'taskProgress', 'taskReview', 'taskRevision', 'taskComplete', 'statusTodo', 'statusProgress', 'statusReview', 'statusRevision', 'statusComplete', 'date'));
    }
}
