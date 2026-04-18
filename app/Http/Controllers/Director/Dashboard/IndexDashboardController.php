<?php

namespace App\Http\Controllers\Director\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Task;
use App\Support\DashboardNonAdminNotifications;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IndexDashboardController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $today = now()->toDateString();
        $user = Auth::user();

        $projects = Project::with(['status', 'difficulty', 'sdms', 'director.division'])
            ->where('id_director', $user->id)
            ->whereHas('status', function ($q) {
                $q->whereIn('class', ['todo', 'running']);
            })
            ->whereDate('end_date', '>=', $today)
            ->orderBy('updated_at', 'desc')
            ->get();

        $tasks = Task::with(['project', 'status', 'difficulty', 'user.division'])
            ->where(function ($q) use ($user) {
                $q->where('id_user', $user->id)
                    ->orWhereHas('project', function ($p) use ($user) {
                        $p->where('id_director', $user->id);
                    });
            })
            ->excludingStandByDifficulty()
            ->whereHas('status', function ($q) {
                $q->whereIn('class', ['todo', 'progress', 'review']);
            })
            ->orderBy('updated_at', 'desc')
            ->get();

        $main = Project::with(['director', 'tasks', 'status', 'difficulty'])
            ->where('id_director', Auth::id())
            ->join('status_projects', 'projects.id_status', '=', 'status_projects.id')
            ->where('status_projects.status', 'Maintenance')
            ->whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today)
            ->select('projects.*')
            ->get();

        $complete = Project::with(['director', 'tasks', 'status', 'difficulty'])
            ->where('id_director', Auth::id())
            ->join('status_projects', 'projects.id_status', '=', 'status_projects.id')
            ->where('status_projects.status', 'Completed')
            ->whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today)
            ->select('projects.*')
            ->get();

        $notify = DashboardNonAdminNotifications::feed($user);

        return view('view.dashboard.index', [
            'projects' => $projects,
            'today' => $today,
            'main' => $main,
            'complete' => $complete,
            'tasks' => $tasks,
            'dashboardNotifications' => $notify['dashboardNotifications'],
            'dashboardNotificationBadgeCount' => $notify['dashboardNotificationBadgeCount'],
        ]);
    }
}
