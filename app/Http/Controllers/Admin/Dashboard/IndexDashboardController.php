<?php

namespace App\Http\Controllers\Admin\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Administration;
use App\Models\StatusAdministration;
use App\Support\DashboardLeftTabsQuery;
use Carbon\Carbon;

class IndexDashboardController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke()
    {
        $today = Carbon::today();

        $pendingStatusId = StatusAdministration::where('name', 'pending')->value('id');
        $pendingAdministrationsCount = 0;
        $pendingAdministrations = collect();
        if ($pendingStatusId) {
            $pendingAdministrationsCount = Administration::where('id_status', $pendingStatusId)->count();
            $pendingAdministrations = Administration::query()
                ->with(['user.division', 'category', 'status'])
                ->where('id_status', $pendingStatusId)
                ->orderByDesc('created_at')
                ->limit(25)
                ->get();
        }

        $dashboardNotifications = collect();
        foreach ($pendingAdministrations as $adm) {
            $dashboardNotifications->push((object) [
                'kind' => 'administration',
                'sort_at' => $adm->created_at,
                'administration' => $adm,
            ]);
        }
        $dashboardNotifications = $dashboardNotifications
            ->sortByDesc(fn ($n) => $n->sort_at?->timestamp ?? 0)
            ->take(35)
            ->values();

        $dashboardNotificationBadgeCount = $pendingAdministrationsCount;

        $leftTabs = DashboardLeftTabsQuery::build($today->toDateString());
        $ready = $leftTabs['ready'];
        $notready = $leftTabs['notready'];
        $standby = $leftTabs['standby'];
        $absent = $leftTabs['absent'];
        $complete = $leftTabs['complete'];

        return view('view.dashboard.index', compact(
            'ready',
            'notready',
            'standby',
            'absent',
            'complete',
            'today',
            'dashboardNotifications',
            'dashboardNotificationBadgeCount'
        ));
    }
}
