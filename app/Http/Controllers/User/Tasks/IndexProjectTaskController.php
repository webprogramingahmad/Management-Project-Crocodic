<?php

namespace App\Http\Controllers\User\Tasks;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Task;
use App\Models\TaskDifficulty;
use App\Support\TaskBucketQuery;
use App\Support\TaskDateRangeFilter;
use App\Support\TaskStatusCatalog;
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
        $dateFilter = TaskDateRangeFilter::fromRequest($request);
        $bucketDateFilters = TaskDateRangeFilter::queryFilters($dateFilter);
        $date = $dateFilter['date'];

        $bucketBase = Task::with(['user', 'difficulty', 'status', 'project'])
            ->where('tasks.id_project', $project->id)
            ->excludingStandByDifficulty();

        $taskTodo = TaskBucketQuery::forTaskQueryByStatusClass($bucketBase, TaskStatusCatalog::TODO, [
            'date_column' => 'tasks.created_at',
        ]);
        $taskProgress = TaskBucketQuery::forTaskQueryByStatusClass($bucketBase, TaskStatusCatalog::PROGRESS, [
            'date_column' => 'tasks.created_at',
        ]);
        $taskReview = TaskBucketQuery::forTaskQueryByStatusClass($bucketBase, TaskStatusCatalog::REVIEW, [
            'date_column' => 'tasks.created_at',
        ]);
        $taskRevision = TaskBucketQuery::forTaskQueryByStatusClass($bucketBase, TaskStatusCatalog::REVISION, [
            'date_column' => 'tasks.created_at',
        ]);
        $taskComplete = TaskBucketQuery::forTaskQueryByStatusClass($bucketBase, TaskStatusCatalog::COMPLETE, array_merge($bucketDateFilters, [
            'date_column' => 'tasks.updated_at',
        ]));

        $difficulties = TaskDifficulty::oldest()
            ->where('difficulty', '!=', 'Stand By')
            ->get();


        $statusMap = TaskStatusCatalog::mapByClass();
        $statusTodo = $statusMap[TaskStatusCatalog::TODO];
        $statusProgress = $statusMap[TaskStatusCatalog::PROGRESS];
        $statusReview = $statusMap[TaskStatusCatalog::REVIEW];
        $statusRevision = $statusMap[TaskStatusCatalog::REVISION];
        $statusComplete = $statusMap[TaskStatusCatalog::COMPLETE];

        return view('view.tasks.index-project', compact('tasks', 'project', 'projects', 'projectAllowsTaskCreation', 'difficulties', 'taskTodo', 'taskProgress', 'taskReview', 'taskRevision', 'taskComplete', 'statusTodo', 'statusProgress', 'statusReview', 'statusRevision', 'statusComplete', 'date', 'dateFilter'));
    }
}
