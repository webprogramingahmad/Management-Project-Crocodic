<?php

namespace App\Http\Controllers\User\Tasks;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\StatusTask;
use App\Models\Task;
use App\Models\TaskDifficulty;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IndexTaskController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $projects = Project::whereHas('sdms', function ($q) {
            $q->where('users.id', Auth::id());
        })->orderBy('name', 'asc')->get();
        $projectsForTaskForms = Project::whereHas('sdms', function ($q) {
            $q->where('users.id', Auth::id());
        })->whereAllowsTaskCreation()->orderBy('name', 'asc')->get();
        $tasks = Task::with(['project', 'status', 'difficulty'])
            ->where('id_user', Auth::id())
            ->excludingStandByDifficulty()
            ->orderBy('created_at', 'desc')
            ->get();
        $projectId = $request->input('project_id');
        $date      = $request->input('date');

        $taskTodo = Task::with(['project', 'difficulty', 'status'])
            ->where('id_user', Auth::id())
            ->excludingStandByDifficulty()
            ->whereHas('status', fn($q) => $q->where('status', 'To Do'))
            ->when($projectId, fn($q) => $q->where('tasks.id_project', $projectId))
            ->when($date, fn($q) => $q->whereDate('tasks.created_at', $date))
            ->get();

        $taskProgress = Task::with(['project', 'difficulty', 'status'])
            ->where('id_user', Auth::id())
            ->excludingStandByDifficulty()
            ->whereHas('status', fn($q) => $q->where('status', 'In progress'))
            ->when($projectId, fn($q) => $q->where('tasks.id_project', $projectId))
            ->when($date, fn($q) => $q->whereDate('tasks.created_at', $date))
            ->get();

        $taskReview = Task::with(['project', 'difficulty', 'status'])
            ->where('id_user', Auth::id())
            ->excludingStandByDifficulty()
            ->whereHas('status', fn($q) => $q->where('status', 'Review'))
            ->when($projectId, fn($q) => $q->where('tasks.id_project', $projectId))
            ->when($date, fn($q) => $q->whereDate('tasks.created_at', $date))
            ->get();

        $taskRevision = Task::with(['project', 'difficulty', 'status'])
            ->where('id_user', Auth::id())
            ->excludingStandByDifficulty()
            ->whereHas('status', fn($q) => $q->where('status', 'Revision'))
            ->when($projectId, fn($q) => $q->where('tasks.id_project', $projectId))
            ->when($date, fn($q) => $q->whereDate('tasks.created_at', $date))
            ->get();

        $taskComplete = Task::with(['project', 'difficulty', 'status'])
            ->where('id_user', Auth::id())
            ->excludingStandByDifficulty()
            ->whereHas('status', fn($q) => $q->where('status', 'Complete'))
            ->when($projectId, fn($q) => $q->where('tasks.id_project', $projectId))
            ->when(
                $date,
                fn($q) =>
                $q->whereDate('tasks.updated_at', $date),
                fn($q) =>
                $q->whereDate('tasks.updated_at', now()->toDateString())
            )
            ->get();

        $difficulties = TaskDifficulty::oldest()
            ->where('difficulty', '!=', 'Stand By')
            ->get();

        $statusTodo     = StatusTask::where('status', 'To Do')->first();
        $statusProgress = StatusTask::where('status', 'In progress')->first();
        $statusReview   = StatusTask::where('status', 'Review')->first();
        $statusRevision = StatusTask::where('status', 'Revision')->first();
        $statusComplete = StatusTask::where('status', 'Complete')->first();

        return view('view.tasks.index', compact('tasks', 'projects', 'projectsForTaskForms', 'taskTodo', 'taskProgress', 'taskReview', 'taskRevision', 'taskComplete', 'statusTodo', 'statusProgress', 'statusReview', 'statusRevision', 'statusComplete', 'difficulties', 'projectId', 'date'));
    }
}
