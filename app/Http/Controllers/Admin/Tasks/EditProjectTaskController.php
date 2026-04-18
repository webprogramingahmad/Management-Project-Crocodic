<?php

namespace App\Http\Controllers\Admin\Tasks;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\StatusTask;
use App\Models\Task;
use App\Models\TaskDifficulty;
use Illuminate\Http\Request;

class EditProjectTaskController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke($idP, $id)
    {
        $project = Project::findOrFail($idP);
        $task = Task::findOrFail($id);
        $difficulties = TaskDifficulty::all();
        $statuses = StatusTask::all();
        $users = $project->sdms;

        return view('view.tasks.edit', compact('project', 'task', 'difficulties', 'statuses', 'users'));
    }
}
