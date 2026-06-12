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

class IndexTaskController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $projects = Project::whereHas('sdms', function ($q) {
            $q->where('users.id', Auth::id());
        })->with('sdms:id,name')->orderBy('name', 'asc')->get();
        $projectsForTaskForms = Project::whereHas('sdms', function ($q) {
            $q->where('users.id', Auth::id());
        })->with('sdms:id,name')->whereAllowsTaskCreation()->orderBy('name', 'asc')->get();
        $projectId = $request->input('project_id');
        $dateFilter = TaskDateRangeFilter::fromRequest($request);
        $bucketDateFilters = TaskDateRangeFilter::queryFilters($dateFilter);
        $date = $dateFilter['date'];

        $bucketFilters = [
            'project_id' => $projectId,
        ];
        $taskTodo = TaskBucketQuery::forUserByStatusClass(Auth::id(), TaskStatusCatalog::TODO, $bucketFilters);
        $taskProgress = TaskBucketQuery::forUserByStatusClass(Auth::id(), TaskStatusCatalog::PROGRESS, $bucketFilters);
        $taskReview = TaskBucketQuery::forUserByStatusClass(Auth::id(), TaskStatusCatalog::REVIEW, $bucketFilters);
        $taskRevision = TaskBucketQuery::forUserByStatusClass(Auth::id(), TaskStatusCatalog::REVISION, $bucketFilters);
        $taskComplete = TaskBucketQuery::forUserByStatusClass(Auth::id(), TaskStatusCatalog::COMPLETE, array_merge($bucketDateFilters, [
            'project_id' => $projectId,
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

        return view('view.tasks.index', compact('projects', 'projectsForTaskForms', 'taskTodo', 'taskProgress', 'taskReview', 'taskRevision', 'taskComplete', 'statusTodo', 'statusProgress', 'statusReview', 'statusRevision', 'statusComplete', 'difficulties', 'projectId', 'date', 'dateFilter'));
    }
}
