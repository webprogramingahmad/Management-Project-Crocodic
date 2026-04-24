<?php

namespace App\Http\Controllers\Admin\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Administration;
use App\Models\Project;
use App\Models\StatusAdministration;
use App\Models\Statussdm;
use App\Models\Task;
use App\Models\User;
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

        $completedNotifyDays = 30;
        $recentCompletedProjectsCount = Project::query()
            ->whereHas('status', function ($q) {
                $q->where('class', 'completed');
            })
            ->where('updated_at', '>=', now()->subDays($completedNotifyDays))
            ->count();

        $recentCompletedProjects = Project::query()
            ->with(['status', 'difficulty', 'director'])
            ->whereHas('status', function ($q) {
                $q->where('class', 'completed');
            })
            ->where('updated_at', '>=', now()->subDays($completedNotifyDays))
            ->orderByDesc('updated_at')
            ->limit(25)
            ->get();

        $dashboardNotifications = collect();
        foreach ($pendingAdministrations as $adm) {
            $dashboardNotifications->push((object) [
                'kind' => 'administration',
                'sort_at' => $adm->created_at,
                'administration' => $adm,
            ]);
        }
        foreach ($recentCompletedProjects as $project) {
            $dashboardNotifications->push((object) [
                'kind' => 'project_completed',
                'sort_at' => $project->updated_at,
                'project' => $project,
            ]);
        }
        $dashboardNotifications = $dashboardNotifications
            ->sortByDesc(fn ($n) => $n->sort_at?->timestamp ?? 0)
            ->take(35)
            ->values();

        $dashboardNotificationBadgeCount = $pendingAdministrationsCount + $recentCompletedProjectsCount;

        $ready = Task::with(['project', 'status', 'difficulty', 'user.division'])
            ->orderBy('created_at', 'desc')
            ->whereDoesntHave('user.administrations', function ($q) use ($today) {
                $q->whereDate('start_date', '<=', $today)
                    ->whereDate('end_date', '>=', $today)
                    ->whereHas('status', function ($q2) {
                        $q2->where('name', 'accept');
                    });
            })
            ->whereHas('status', function ($q) {
                $q->where(function ($w) {
                    $w->whereIn('class', ['todo', 'progress', 'review', 'revision'])
                        ->orWhereIn('status', ['To Do', 'In progress', 'Review', 'Revision']);
                });
            })
            ->excludingStandByDifficulty()
            ->select('tasks.*')
            ->get();

        $notReadyStatus = Statussdm::where('status_sdm', 'Not Ready')->first();
        $notready = collect();
        if ($notReadyStatus) {
            $notready = User::with(['division', 'role'])
                ->whereHas('role', function ($q) {
                    $q->whereIn('role', ['staff', 'director']);
                })
                ->where('id_activity_status_sdm', $notReadyStatus->id)
                ->whereDoesntHave('administrations', function ($q) use ($today) {
                    $q->whereDate('start_date', '<=', $today)
                        ->whereDate('end_date', '>=', $today)
                        ->whereHas('status', function ($q2) {
                            $q2->where('name', 'accept');
                        });
                })
                ->orderBy('name', 'asc')
                ->get();
        }
        $standbyStatus = Statussdm::where('status_sdm', 'Stand By')->first();
        $standby = collect();
        if ($standbyStatus) {
            $standby = User::with(['division', 'role'])
                ->whereHas('role', function ($q) {
                    $q->whereIn('role', ['staff', 'director']);
                })
                ->where('id_activity_status_sdm', $standbyStatus->id)
                ->whereDoesntHave('administrations', function ($q) use ($today) {
                    $q->whereDate('start_date', '<=', $today)
                        ->whereDate('end_date', '>=', $today)
                        ->whereHas('status', function ($q2) {
                            $q2->where('name', 'accept');
                        });
                })
                ->orderBy('name', 'asc')
                ->get();
        }
        $absent = User::whereHas('administrations', function ($q) use ($today) {
            $q->whereDate('start_date', '<=', $today)
                ->whereDate('end_date', '>=', $today)
                ->whereHas('status', function ($q2) {
                    $q2->where('name', 'accept');
                });
        })
            ->with(['administrations' => function ($q) use ($today) {
                $q->whereDate('start_date', '<=', $today)
                    ->whereDate('end_date', '>=', $today)
                    ->whereHas('status', function ($q2) {
                        $q2->where('name', 'accept');
                    });
            }, 'administrations.status'])
            ->get();
        $complete = Task::with(['project', 'difficulty', 'status'])
            ->join('status_tasks', 'status_tasks.id', '=', 'tasks.id_status')
            ->where('status_tasks.status', 'Complete')
            ->whereDate('tasks.updated_at', now()->toDateString())
            ->excludingStandByDifficulty()
            ->select('tasks.*')
            ->get();

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
