<?php

namespace App\Support;

use App\Models\Statussdm;
use App\Models\StatusAdministration;
use App\Models\Task;
use App\Models\User;

class DashboardLeftTabsQuery
{
    /**
     * @return array{
     *   ready:\Illuminate\Database\Eloquent\Collection<int, \App\Models\Task>,
     *   notready:\Illuminate\Database\Eloquent\Collection<int, \App\Models\User>,
     *   standby:\Illuminate\Database\Eloquent\Collection<int, \App\Models\User>,
     *   absent:\Illuminate\Database\Eloquent\Collection<int, \App\Models\User>,
     *   complete:\Illuminate\Database\Eloquent\Collection<int, \App\Models\Task>
     * }
     */
    public static function build(string $today): array
    {
        $acceptedStatusId = StatusAdministration::query()
            ->where('name', 'accept')
            ->value('id');
        $statusMap = Statussdm::query()
            ->whereIn('status_sdm', ['Not Ready', 'Stand By'])
            ->pluck('id', 'status_sdm');

        $absenceConstraint = function ($q) use ($today, $acceptedStatusId) {
            $q->whereDate('start_date', '<=', $today)
                ->whereDate('end_date', '>=', $today);

            if ($acceptedStatusId) {
                $q->where('id_status', $acceptedStatusId);
            } else {
                // Keep old behavior safety: if "accept" status row is missing, no one should match "absent".
                $q->whereRaw('1 = 0');
            }
        };

        $ready = Task::with(['project', 'status', 'difficulty', 'user.division', 'user.role'])
            ->orderBy('created_at', 'desc')
            ->whereDoesntHave('user.administrations', $absenceConstraint)
            ->whereHas('status', function ($q) {
                $q->where(function ($w) {
                    $w->whereIn('class', ['todo', 'progress', 'review', 'revision'])
                        ->orWhereIn('status', ['To Do', 'In progress', 'Review', 'Revision']);
                });
            })
            ->excludingStandByDifficulty()
            ->select('tasks.*')
            ->get();

        $notReadyStatusId = $statusMap['Not Ready'] ?? null;
        $notready = collect();
        if ($notReadyStatusId) {
            $notready = User::with(['division', 'role'])
                ->whereHas('role', function ($q) {
                    $q->whereIn('role', ['staff', 'director']);
                })
                ->where('id_activity_status_sdm', $notReadyStatusId)
                ->whereDoesntHave('administrations', $absenceConstraint)
                ->orderBy('name', 'asc')
                ->get();
        }

        $standbyStatusId = $statusMap['Stand By'] ?? null;
        $standby = collect();
        if ($standbyStatusId) {
            $standby = User::with(['division', 'role'])
                ->whereHas('role', function ($q) {
                    $q->whereIn('role', ['staff', 'director']);
                })
                ->where('id_activity_status_sdm', $standbyStatusId)
                ->whereDoesntHave('administrations', $absenceConstraint)
                ->orderBy('name', 'asc')
                ->get();
        }

        $absent = User::whereHas('administrations', $absenceConstraint)
            ->with(['administrations' => function ($q) use ($today, $acceptedStatusId) {
                $q->whereDate('start_date', '<=', $today)
                    ->whereDate('end_date', '>=', $today);
                if ($acceptedStatusId) {
                    $q->where('id_status', $acceptedStatusId);
                } else {
                    $q->whereRaw('1 = 0');
                }
            }, 'administrations.status', 'administrations.category'])
            ->get();

        $complete = Task::with(['project', 'difficulty', 'status', 'user.division', 'user.role'])
            ->join('status_tasks', 'status_tasks.id', '=', 'tasks.id_status')
            ->where(function ($q) {
                $q->where('status_tasks.class', 'complete')
                    ->orWhere('status_tasks.status', 'Complete');
            })
            ->whereDate('tasks.updated_at', now()->toDateString())
            ->excludingStandByDifficulty()
            ->select('tasks.*')
            ->get();

        return compact('ready', 'notready', 'standby', 'absent', 'complete');
    }
}

