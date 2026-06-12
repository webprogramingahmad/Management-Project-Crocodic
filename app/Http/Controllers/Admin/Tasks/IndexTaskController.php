<?php

namespace App\Http\Controllers\Admin\Tasks;

use App\Models\Project;
use App\Models\Task;
use App\Models\TaskDifficulty;
use App\Support\TaskBucketQuery;
use App\Support\TaskDateRangeFilter;
use App\Support\TaskStatusCatalog;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class IndexTaskController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $projects = Project::with('sdms:id,name')->orderBy('name', 'asc')->get();
        $projectsForTaskForms = Project::with('sdms:id,name')->whereAllowsTaskCreation()->orderBy('name', 'asc')->get();
        $projectId = $request->input('project_id');
        $dateFilter = TaskDateRangeFilter::fromRequest($request);
        $bucketDateFilters = TaskDateRangeFilter::queryFilters($dateFilter);
        $date = $dateFilter['date'];
        $bucketBase = Task::query()->excludingStandByDifficulty();
        $taskTodo = TaskBucketQuery::forTaskQueryByStatusClass($bucketBase, TaskStatusCatalog::TODO, [
            'project_id' => $projectId,
            'date_column' => 'tasks.updated_at',
        ]);
        $taskProgress = TaskBucketQuery::forTaskQueryByStatusClass($bucketBase, TaskStatusCatalog::PROGRESS, [
            'project_id' => $projectId,
            'date_column' => 'tasks.updated_at',
        ]);
        $taskReview = TaskBucketQuery::forTaskQueryByStatusClass($bucketBase, TaskStatusCatalog::REVIEW, [
            'project_id' => $projectId,
            'date_column' => 'tasks.updated_at',
        ]);
        $taskRevision = TaskBucketQuery::forTaskQueryByStatusClass($bucketBase, TaskStatusCatalog::REVISION, [
            'project_id' => $projectId,
            'date_column' => 'tasks.updated_at',
        ]);
        $taskComplete = TaskBucketQuery::forTaskQueryByStatusClass($bucketBase, TaskStatusCatalog::COMPLETE, array_merge($bucketDateFilters, [
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

        return view('view.tasks.index', compact('projects', 'projectsForTaskForms', 'difficulties', 'taskTodo', 'taskProgress', 'taskReview', 'taskRevision', 'taskComplete', 'statusTodo', 'statusProgress', 'statusReview', 'statusRevision', 'statusComplete', 'projectId', 'date', 'dateFilter'));
    }
}
