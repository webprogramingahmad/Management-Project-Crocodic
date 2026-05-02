<?php

namespace App\Http\Controllers\Director\Tasks;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Task;
use App\Models\TaskDifficulty;
use App\Support\TaskBucketQuery;
use App\Support\TaskStatusCatalog;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IndexProjectTaskController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, $id)
    {
        $today = now()->toDateString();
        $project = Project::with([
            'status',
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
        ])->findOrFail($id);

        abort_unless($project->id_director === Auth::id(), 403);
        $project->sdms->each(function ($sdm) {
            $activeAdm = $sdm->administrations->first();
            if (!$activeAdm || !$activeAdm->end_date) {
                return;
            }
            $returnDate = Carbon::parse($activeAdm->end_date)->addDay();
            $sdm->is_absent_now = true;
            $sdm->absent_returns_on_label = $returnDate->translatedFormat('j M');
        });

        $projectAllowsTaskCreation = $project->allowsTaskCreation();
        $tasks = Task::with(['user', 'difficulty', 'status', 'project'])
            ->where('id_project', $id)
            ->excludingStandByDifficulty()
            ->get();
        $date      = $request->input('date');

        $bucketBase = Task::with(['user', 'difficulty', 'status', 'project'])
            ->where('tasks.id_project', $project->id)
            ->excludingStandByDifficulty();

        $taskTodo = TaskBucketQuery::forTaskQueryByStatusClass($bucketBase, TaskStatusCatalog::TODO, [
            'date' => $date,
            'date_column' => 'tasks.created_at',
        ]);
        $taskProgress = TaskBucketQuery::forTaskQueryByStatusClass($bucketBase, TaskStatusCatalog::PROGRESS, [
            'date' => $date,
            'date_column' => 'tasks.created_at',
        ]);
        $taskReview = TaskBucketQuery::forTaskQueryByStatusClass($bucketBase, TaskStatusCatalog::REVIEW, [
            'date' => $date,
            'date_column' => 'tasks.created_at',
        ]);
        $taskRevision = TaskBucketQuery::forTaskQueryByStatusClass($bucketBase, TaskStatusCatalog::REVISION, [
            'date' => $date,
            'date_column' => 'tasks.created_at',
        ]);
        $taskComplete = TaskBucketQuery::forTaskQueryByStatusClass($bucketBase, TaskStatusCatalog::COMPLETE, [
            'date' => $date,
            'date_column' => 'tasks.updated_at',
            'default_date' => now()->toDateString(),
        ]);

        $difficulties = TaskDifficulty::oldest()
            ->where('difficulty', '!=', 'Stand By')
            ->get();

        $statusMap = TaskStatusCatalog::mapByClass();
        $statusTodo = $statusMap[TaskStatusCatalog::TODO];
        $statusProgress = $statusMap[TaskStatusCatalog::PROGRESS];
        $statusReview = $statusMap[TaskStatusCatalog::REVIEW];
        $statusRevision = $statusMap[TaskStatusCatalog::REVISION];
        $statusComplete = $statusMap[TaskStatusCatalog::COMPLETE];

        return view('view.tasks.index-project', compact('tasks', 'project', 'projectAllowsTaskCreation', 'difficulties', 'taskTodo', 'taskProgress', 'taskReview', 'taskRevision', 'taskComplete', 'statusTodo', 'statusProgress', 'statusReview', 'statusRevision', 'statusComplete', 'date'));
    }
}
