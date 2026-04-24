<?php

namespace App\Http\Controllers\User\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Statussdm;
use App\Models\Task;
use App\Models\User;
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
                $q->whereIn('class', ['todo', 'progress', 'review', 'revision']);
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

        // Unified left tabs (same as executive): Ready / Stand by / Not ready / Complete / Absent
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
            }, 'administrations.status', 'administrations.category'])
            ->get();

        $complete = Task::with(['project', 'difficulty', 'status', 'user.division'])
            ->join('status_tasks', 'status_tasks.id', '=', 'tasks.id_status')
            ->where('status_tasks.status', 'Complete')
            ->whereDate('tasks.updated_at', now()->toDateString())
            ->excludingStandByDifficulty()
            ->select('tasks.*')
            ->get();

        $notify = DashboardNonAdminNotifications::feed($user);

        return view('view.dashboard.index', [
            'projects' => $projects,
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
