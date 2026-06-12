<?php

namespace App\Http\Controllers\Admin\Project;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Task;
use App\Support\ProjectReportAccess;
use App\Support\TaskRunningTimer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ShowReportTaskController extends Controller
{
    /**
     * Detail task dalam konteks Project Report (read-only).
     */
    public function __invoke(Request $request, string $id, string $taskId)
    {
        $actor = Auth::user();
        $role = $actor->role?->role;

        abort_unless(in_array($role, ['executive', 'director'], true), 403);

        $project = Project::with(['difficulty', 'status', 'director'])->findOrFail($id);

        ProjectReportAccess::assertCanView($actor, $project);

        $task = Task::with([
            'user.division',
            'creator',
            'difficulty',
            'status',
            'photos.uploader',
            'revisionCycles',
        ])
            ->where('id_project', $project->id)
            ->excludingStandByDifficulty()
            ->findOrFail($taskId);

        $onTime = TaskRunningTimer::reviewedOnTime($task);
        $reportRoute = ProjectReportAccess::reportRouteName($role);
        $reportTaskRoute = ProjectReportAccess::reportTaskRouteName($role);

        return view('view.projects.report-task', compact(
            'project',
            'task',
            'onTime',
            'reportRoute',
            'reportTaskRoute',
        ));
    }
}
