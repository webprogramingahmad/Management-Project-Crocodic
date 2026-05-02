<?php

namespace App\Http\Controllers\Director\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Task;
use App\Support\DashboardLeftTabsQuery;
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

        // Kartu notifikasi Tasks: hanya task yang di-assign ke director itu sendiri
        // (bukan task staff di proyek yang sama).
        $tasks = Task::with(['project', 'status', 'difficulty', 'user.division'])
            ->where('id_user', $user->id)
            ->excludingStandByDifficulty()
            ->whereHas('status', function ($q) {
                $q->whereIn('class', ['todo', 'progress', 'review', 'revision']);
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

        // Unified left tabs (same as executive): Ready / Stand by / Not ready / Complete / Absent
        $leftTabs = DashboardLeftTabsQuery::build($today);
        $ready = $leftTabs['ready'];
        $notready = $leftTabs['notready'];
        $standby = $leftTabs['standby'];
        $absent = $leftTabs['absent'];
        $complete = $leftTabs['complete'];

        $notify = DashboardNonAdminNotifications::feed($user);

        return view('view.dashboard.index', [
            'projects' => $projects,
            'today' => $today,
            'main' => $main,
            'complete' => $complete,
            'tasks' => $tasks,
            'ready' => $ready,
            'notready' => $notready,
            'standby' => $standby,
            'absent' => $absent,
            'dashboardNotifications' => $notify['dashboardNotifications'],
            'dashboardNotificationBadgeCount' => $notify['dashboardNotificationBadgeCount'],
        ]);
    }
}
