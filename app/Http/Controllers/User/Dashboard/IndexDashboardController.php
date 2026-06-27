<?php

namespace App\Http\Controllers\User\Dashboard;

use App\Http\Controllers\Controller;
use App\Support\DashboardLeftTabsQuery;
use App\Support\DashboardNonAdminNotifications;
use App\Support\StaffDashboardQuery;
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

        $tasks = StaffDashboardQuery::activeTasksForUser($user->id);
        $projects = StaffDashboardQuery::activeProjectsForUser($user->id, $today);

        $leftTabs = DashboardLeftTabsQuery::build($today);
        $ready = $leftTabs['ready'];
        $notready = $leftTabs['notready'];
        $standby = $leftTabs['standby'];
        $absent = $leftTabs['absent'];
        $complete = $leftTabs['complete'];

        $notify = DashboardNonAdminNotifications::feed($user);

        return view('view.dashboard.index', [
            'projects' => $projects,
            'main' => collect(),
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
