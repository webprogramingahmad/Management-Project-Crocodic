<?php

namespace App\Http\Controllers\Admin\Tasks;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\StatusTask;
use App\Models\TaskDifficulty;

class CreateProjectTaskController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke($id)
    {
        $project = Project::findOrFail($id);
        $difficulties = TaskDifficulty::all();
        $statuses = StatusTask::all();
        $users = $project->sdms;

        return view('view.tasks.create', compact('project', 'difficulties', 'statuses', 'users'));
    }
}
