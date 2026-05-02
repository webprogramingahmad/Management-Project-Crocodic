<?php

namespace App\Support;

use App\Models\Administration;
use App\Models\NotificationRead;
use App\Models\Project;
use App\Models\StatusAdministration;
use App\Models\Task;
use App\Models\User;

class DashboardNonAdminNotifications
{
    /**
     * Feed kanan dashboard (director/user): izin pending (milik user) + project completed (30 hari), sama pola dengan admin.
     *
     * @return array{dashboardNotifications: \Illuminate\Support\Collection, dashboardNotificationBadgeCount: int}
     */
    public static function feed(User $user): array
    {
        $user->loadMissing('role');

        $acceptStatusId = StatusAdministration::where('name', 'accept')->value('id');
        $rejectStatusId = StatusAdministration::where('name', 'reject')->value('id');
        $administrationNotifications = Administration::query()
            ->with(['user.division', 'category', 'status'])
            ->where('id_user', $user->id)
            ->when($acceptStatusId || $rejectStatusId, function ($query) use ($acceptStatusId, $rejectStatusId) {
                $query->whereIn('id_status', array_values(array_filter([$acceptStatusId, $rejectStatusId])));
            })
            ->orderByDesc('updated_at')
            ->limit(25)
            ->get();

        $reviewTasks = collect();
        if ($user->role?->role === 'director') {
            $reviewTasks = Task::query()
                ->with(['project', 'status', 'user.division'])
                ->whereHas('status', function ($q) {
                    $q->whereClassOrLegacy('review');
                })
                ->whereHas('project', function ($q) use ($user) {
                    $q->where('id_director', $user->id);
                })
                ->where('id_user', '!=', $user->id)
                ->orderByDesc('running_review_at')
                ->orderByDesc('updated_at')
                ->limit(25)
                ->get();
        }

        $deadlineAlertTasks = Task::query()
            ->with(['project', 'status', 'difficulty'])
            ->where('id_user', $user->id)
            ->whereHas('status', function ($q) {
                $q->whereIn('class', ['progress', 'revision'])
                    ->orWhereRaw('LOWER(status) IN (?, ?)', ['in progress', 'revision']);
            })
            ->orderByDesc('updated_at')
            ->limit(50)
            ->get()
            ->map(function (Task $task) {
                $deadline = TaskRunningTimer::deadlineFor($task);
                if (!$deadline) {
                    return null;
                }

                $balanceSeconds = (int) ($deadline->getTimestamp() - now()->timezone(config('app.timezone'))->getTimestamp());
                if ($balanceSeconds > 1800) {
                    return null;
                }

                return (object) [
                    'task' => $task,
                    'balance_seconds' => $balanceSeconds,
                    'sort_at' => $task->updated_at,
                ];
            })
            ->filter()
            ->values();

        $projectTimelineAlerts = collect();
        if ($user->role?->role === 'director') {
            $projectTimelineAlerts = Project::query()
                ->with(['status', 'director'])
                ->where('id_director', $user->id)
                ->whereHas('status', function ($q) {
                    $q->whereIn('class', ['todo', 'running'])
                        ->orWhereRaw('LOWER(status) IN (?, ?)', ['to do', 'running']);
                })
                ->orderByDesc('updated_at')
                ->limit(50)
                ->get()
                ->map(function (Project $project) {
                    $todoBalance = ProjectTimelineTimer::todoStartBalanceSeconds($project);
                    if ($todoBalance !== null && $todoBalance <= 86400) {
                        return (object) [
                            'project' => $project,
                            'phase' => 'start',
                            'balance_seconds' => $todoBalance,
                            'sort_at' => $project->updated_at,
                        ];
                    }

                    $runningBalance = ProjectTimelineTimer::runningEndBalanceSeconds($project);
                    if ($runningBalance !== null && $runningBalance <= 86400) {
                        return (object) [
                            'project' => $project,
                            'phase' => 'end',
                            'balance_seconds' => $runningBalance,
                            'sort_at' => $project->updated_at,
                        ];
                    }

                    return null;
                })
                ->filter()
                ->values();
        }

        $dashboardNotifications = collect();
        foreach ($administrationNotifications as $adm) {
            $dashboardNotifications->push((object) [
                'kind' => 'administration',
                'sort_at' => $adm->updated_at ?? $adm->created_at,
                'administration' => $adm,
                'read_key' => self::buildReadKey(
                    'administration',
                    (string) $adm->id,
                    (string) ($adm->status?->name ?? 'pending'),
                    $adm->updated_at ?? $adm->created_at
                ),
            ]);
        }
        foreach ($reviewTasks as $task) {
            $dashboardNotifications->push((object) [
                'kind' => 'task_review',
                'sort_at' => $task->running_review_at ?? $task->updated_at,
                'task' => $task,
                'read_key' => self::buildReadKey(
                    'task_review',
                    (string) $task->id,
                    'review',
                    $task->running_review_at ?? $task->updated_at
                ),
            ]);
        }
        foreach ($deadlineAlertTasks as $deadlineAlert) {
            $dashboardNotifications->push((object) [
                'kind' => 'task_deadline_alert',
                'sort_at' => $deadlineAlert->sort_at,
                'task' => $deadlineAlert->task,
                'balance_seconds' => $deadlineAlert->balance_seconds,
                // Dynamic by condition; tidak disimpan sebagai read/click-dismiss.
                'read_key' => null,
            ]);
        }
        foreach ($projectTimelineAlerts as $projectAlert) {
            $dashboardNotifications->push((object) [
                'kind' => 'project_timeline_alert',
                'sort_at' => $projectAlert->sort_at,
                'project' => $projectAlert->project,
                'phase' => $projectAlert->phase,
                'balance_seconds' => $projectAlert->balance_seconds,
                // Dynamic by condition; tidak disimpan sebagai read/click-dismiss.
                'read_key' => null,
            ]);
        }

        $readKeys = NotificationRead::query()
            ->where('id_user', $user->id)
            ->whereIn('notification_key', $dashboardNotifications->pluck('read_key')->filter()->all())
            ->pluck('notification_key')
            ->all();

        $readLookup = array_fill_keys($readKeys, true);
        $dashboardNotifications = $dashboardNotifications
            ->reject(fn ($note) => isset($readLookup[$note->read_key ?? '']));

        $dashboardNotifications = $dashboardNotifications
            ->sortByDesc(fn ($n) => $n->sort_at?->timestamp ?? 0)
            ->take(35)
            ->values();

        $dashboardNotificationBadgeCount = $dashboardNotifications->count();

        return [
            'dashboardNotifications' => $dashboardNotifications,
            'dashboardNotificationBadgeCount' => $dashboardNotificationBadgeCount,
        ];
    }

    private static function buildReadKey(string $kind, string $entityId, string $state, $at): string
    {
        $timestamp = $at?->timestamp ?? 0;

        return implode(':', [$kind, $entityId, $state, (string) $timestamp]);
    }
}
