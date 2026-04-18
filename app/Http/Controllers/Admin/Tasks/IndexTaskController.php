<?php

namespace App\Http\Controllers\Admin\Tasks;

use App\Models\Project;
use App\Models\StatusTask;
use App\Models\Task;
use App\Models\TaskDifficulty;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

class IndexTaskController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $projects = Project::with('sdms.division')->orderBy('name', 'asc')->get();
        $projectsForTaskForms = Project::with('sdms.division')->whereAllowsTaskCreation()->orderBy('name', 'asc')->get();
        $projectId = $request->input('project_id');
        $date      = $request->input('date');
        $tasks = Task::with(['project', 'status', 'difficulty', 'user'])
            ->excludingStandByDifficulty()
            ->orderBy('created_at', 'desc')
            ->get();
        $taskTodo = Task::with(['project', 'difficulty', 'status'])
            ->join('status_tasks', 'status_tasks.id', '=', 'tasks.id_status')
            ->where('status_tasks.status', 'To Do')
            ->excludingStandByDifficulty()
            ->when($projectId, fn($q) => $q->where('tasks.id_project', $projectId))
            ->when($date, fn($q) => $q->whereDate('tasks.updated_at', $date))
            ->select('tasks.*')
            ->get();
        $taskProgress = Task::with(['project', 'difficulty', 'status'])
            ->join('status_tasks', 'status_tasks.id', '=', 'tasks.id_status')
            ->where('status_tasks.status', 'In progress')
            ->excludingStandByDifficulty()
            ->when($projectId, fn($q) => $q->where('tasks.id_project', $projectId))
            ->when($date, fn($q) => $q->whereDate('tasks.updated_at', $date))
            ->select('tasks.*')
            ->get();
        $taskReview = Task::with(['project', 'difficulty', 'status'])
            ->join('status_tasks', 'status_tasks.id', '=', 'tasks.id_status')
            ->where('status_tasks.status', 'Review')
            ->excludingStandByDifficulty()
            ->when($projectId, fn($q) => $q->where('tasks.id_project', $projectId))
            ->when($date, fn($q) => $q->whereDate('tasks.updated_at', $date))
            ->select('tasks.*')
            ->get();
        $taskComplete = Task::with(['project', 'difficulty', 'status'])
            ->join('status_tasks', 'status_tasks.id', '=', 'tasks.id_status')
            ->where('status_tasks.status', 'Complete')
            ->excludingStandByDifficulty()
            ->when($projectId, fn($q) => $q->where('tasks.id_project', $projectId))
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
        $statusComplete = StatusTask::where('status', 'Complete')->first();

        return view('view.tasks.index', compact('tasks', 'projects', 'projectsForTaskForms', 'difficulties', 'taskTodo', 'taskProgress', 'taskReview', 'taskComplete', 'statusTodo', 'statusProgress', 'statusReview', 'statusComplete', 'projectId', 'date'));
    }
}
