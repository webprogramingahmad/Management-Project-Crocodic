<?php

namespace App\Http\Controllers\Admin\Tasks;

use App\Models\Project;
use App\Models\StatusTask;
use App\Models\Task;
use App\Models\TaskDifficulty;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class IndexProjectTaskController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, $id)
    {
        $project = Project::with(['sdms', 'status'])->findOrFail($id);
        $projectAllowsTaskCreation = $project->allowsTaskCreation();
        $tasks = Task::with(['user', 'difficulty', 'status', 'project'])
            ->where('id_project', $id)
            ->excludingStandByDifficulty()
            ->get();
        $projects = Project::orderBy('name', 'asc')->get();
        $date      = $request->input('date');

        $statusTodo     = StatusTask::where('status', 'To Do')->first();
        $statusProgress = StatusTask::where('status', 'In progress')->first();
        $statusReview   = StatusTask::where('status', 'Review')->first();
        $statusRevision = StatusTask::where('status', 'Revision')->first();
        $statusComplete = StatusTask::where('status', 'Complete')->first();

        $difficulties = TaskDifficulty::oldest()
            ->where('difficulty', '!=', 'Stand By')
            ->get();

        $taskTodo = Task::with(['user', 'difficulty', 'status', 'project'])
            ->where('tasks.id_project', $project->id)
            ->excludingStandByDifficulty()
            ->join('status_tasks', 'status_tasks.id', '=', 'tasks.id_status')
            ->when($date, fn($q) => $q->whereDate('tasks.created_at', $date))
            ->where('status_tasks.status', 'To Do')
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

        return view('view.tasks.index-project', compact('tasks', 'project', 'projects', 'projectAllowsTaskCreation', 'difficulties', 'taskTodo', 'taskProgress', 'taskReview', 'taskRevision', 'taskComplete', 'statusTodo', 'statusProgress', 'statusReview', 'statusRevision', 'statusComplete', 'date'));
    }
}
