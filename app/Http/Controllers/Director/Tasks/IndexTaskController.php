<?php

namespace App\Http\Controllers\Director\Tasks;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Task;
use App\Models\TaskDifficulty;
use App\Support\TaskBucketQuery;
use App\Support\TaskDateRangeFilter;
use App\Support\TaskStatusCatalog;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IndexTaskController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $projects = Project::with('sdms:id,name')->where('id_director', Auth::id())
            ->orderBy('name', 'asc')
            ->get();
        $today = now()->toDateString();
        $projectsForTaskForms = Project::with([
            'sdms' => function ($q) use ($today) {
                $q->select('users.id', 'users.name', 'users.id_activity_status_sdm')
                    ->with([
                        'activityStatussdm:id,status_sdm',
                        'administrations' => function ($aq) use ($today) {
                            $aq->select('id', 'id_user', 'end_date')
                                ->whereDate('start_date', '<=', $today)
                                ->whereDate('end_date', '>=', $today)
                                ->whereHas('status', function ($sq) {
                                    $sq->where('name', 'accept');
                                })
                                ->orderByDesc('end_date');
                        },
                    ]);
            },
        ])
            ->where('id_director', Auth::id())
            ->whereAllowsTaskCreation()
            ->orderBy('name', 'asc')
            ->get();
        $projectsForTaskForms->each(function ($project) {
            $project->sdms->each(function ($sdm) {
                $activeAdm = $sdm->administrations->first();
                if (!$activeAdm || !$activeAdm->end_date) {
                    return;
                }
                $returnDate = Carbon::parse($activeAdm->end_date)->addDay();
                $sdm->is_absent_now = true;
                $sdm->absent_returns_on_label = $returnDate->translatedFormat('j M');
            });
        });
        $projectId = $request->input('project_id');
        $dateFilter = TaskDateRangeFilter::fromRequest($request);
        $bucketDateFilters = TaskDateRangeFilter::queryFilters($dateFilter);
        $date = $dateFilter['date'];
        $todoBase = Task::query()
            ->where(function ($q) {
                $q->whereHas('project', function ($projectQuery) {
                    $projectQuery->where('projects.id_director', Auth::id())
                      ->orWhereHas('sdms', function ($qq) {
                          $qq->where('users.id', Auth::id());
                      });
                })->orWhere('tasks.id_user', Auth::id());
            })
            ->excludingStandByDifficulty();

        $workBase = Task::query()
            ->whereHas('project', function ($q) {
                $q->where('projects.id_director', Auth::id())
                  ->orWhereHas('sdms', function ($qq) {
                      $qq->where('users.id', Auth::id());
                  });
            })
            ->excludingStandByDifficulty();

        $taskTodo = TaskBucketQuery::forTaskQueryByStatusClass($todoBase, TaskStatusCatalog::TODO, [
            'project_id' => $projectId,
        ]);
        $taskProgress = TaskBucketQuery::forTaskQueryByStatusClass($workBase, TaskStatusCatalog::PROGRESS, [
            'project_id' => $projectId,
        ]);
        $taskReview = TaskBucketQuery::forTaskQueryByStatusClass($workBase, TaskStatusCatalog::REVIEW, [
            'project_id' => $projectId,
        ]);
        $taskRevision = TaskBucketQuery::forTaskQueryByStatusClass($workBase, TaskStatusCatalog::REVISION, [
            'project_id' => $projectId,
        ]);
        $taskComplete = TaskBucketQuery::forTaskQueryByStatusClass($workBase, TaskStatusCatalog::COMPLETE, array_merge($bucketDateFilters, [
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
