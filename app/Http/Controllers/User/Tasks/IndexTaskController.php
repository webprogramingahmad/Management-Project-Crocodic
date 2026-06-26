<?php

namespace App\Http\Controllers\User\Tasks;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Support\TaskBoardReferenceData;
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
        $today = now()->toDateString();

        $projects = Project::whereHas('sdms', function ($q) {
            $q->where('users.id', Auth::id());
        })->with([
            'status',
            'sdms' => function ($q) use ($today) {
                $q->select('users.id', 'users.name')
                    ->with(['administrations' => function ($aq) use ($today) {
                        $aq->select('id', 'id_user', 'end_date')
                            ->whereDate('start_date', '<=', $today)
                            ->whereDate('end_date', '>=', $today)
                            ->whereHas('status', function ($sq) {
                                $sq->where('name', 'accept');
                            })
                            ->orderByDesc('end_date');
                    }]);
            },
        ])->orderBy('name', 'asc')->get();

        TaskBoardReferenceData::decorateProjectsWithAbsentLabels($projects);

        $projectsForTaskForms = $projects
            ->filter(fn (Project $project) => $project->allowsTaskCreation())
            ->values();

        $projectId = $request->input('project_id');
        $dateFilter = TaskDateRangeFilter::fromRequest($request);
        $bucketDateFilters = TaskDateRangeFilter::queryFilters($dateFilter);
        $date = $dateFilter['date'];

        $bucketFilters = [
            'project_id' => $projectId,
        ];

        $boardColumns = TaskBucketQuery::forUserBoardColumns(
            Auth::id(),
            $bucketFilters,
            $bucketDateFilters
        );

        $taskTodo = $boardColumns['todo'];
        $taskProgress = $boardColumns['progress'];
        $taskReview = $boardColumns['review'];
        $taskRevision = $boardColumns['revision'];
        $taskComplete = $boardColumns['complete'];

        $difficulties = TaskBoardReferenceData::difficultiesForForms();

        $statusMap = TaskStatusCatalog::mapByClass();
        $statusTodo = $statusMap[TaskStatusCatalog::TODO];
        $statusProgress = $statusMap[TaskStatusCatalog::PROGRESS];
        $statusReview = $statusMap[TaskStatusCatalog::REVIEW];
        $statusRevision = $statusMap[TaskStatusCatalog::REVISION];
        $statusComplete = $statusMap[TaskStatusCatalog::COMPLETE];

        return view('view.tasks.index', compact('projects', 'projectsForTaskForms', 'taskTodo', 'taskProgress', 'taskReview', 'taskRevision', 'taskComplete', 'statusTodo', 'statusProgress', 'statusReview', 'statusRevision', 'statusComplete', 'difficulties', 'projectId', 'date', 'dateFilter'));
    }
}
