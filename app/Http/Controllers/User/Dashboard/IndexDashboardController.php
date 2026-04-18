<?php

namespace App\Http\Controllers\User\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Task;
use App\Support\DashboardNonAdminNotifications;
use Illuminate\Support\Facades\Auth;

class IndexDashboardController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke()
    {
        $today = now()->toDateString();
        $user = Auth::user();

        $tasks = Task::with(['project', 'status', 'difficulty', 'user.division'])
            ->where('id_user', $user->id)
            ->excludingStandByDifficulty()
            ->whereHas('status', function ($q) {
                $q->whereIn('class', ['todo', 'progress', 'review']);
            })
            ->orderBy('updated_at', 'desc')
            ->get();

        $projects = Project::with(['director.division', 'tasks', 'status', 'difficulty'])
            ->whereHas('sdms', function ($q) use ($user) {
                $q->where('users.id', $user->id);
            })
            ->whereHas('status', function ($q) {
                $q->whereIn('class', ['todo', 'running']);
            })
            ->whereDate('end_date', '>=', $today)
            ->orderBy('created_at', 'desc')
            ->get();

        $main = Project::with(['director', 'tasks', 'status', 'difficulty'])
            ->join('project_user', 'project_user.project_id', 'projects.id')
            ->join('users', 'users.id', 'project_user.user_id')
            ->join('status_projects', 'projects.id_status', '=', 'status_projects.id')
            ->where('status_projects.status', 'Maintenance')
            ->where('users.id', $user->id)
            ->whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today)
            ->select('projects.*')
            ->orderBy('created_at', 'desc')
            ->get();

        $complete = Project::with(['director', 'tasks', 'status', 'difficulty'])
            ->join('project_user', 'project_user.project_id', 'projects.id')
            ->join('users', 'users.id', 'project_user.user_id')
            ->join('status_projects', 'projects.id_status', '=', 'status_projects.id')
            ->where('status_projects.status', 'Completed')
            ->where('users.id', $user->id)
            ->whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today)
            ->select('projects.*')
            ->orderBy('created_at', 'desc')
            ->get();

        $notify = DashboardNonAdminNotifications::feed($user);

        return view('view.dashboard.index', [
            'projects' => $projects,
            'main' => $main,
            'complete' => $complete,
            'tasks' => $tasks,
            'dashboardNotifications' => $notify['dashboardNotifications'],
            'dashboardNotificationBadgeCount' => $notify['dashboardNotificationBadgeCount'],
        ]);
    }
}
