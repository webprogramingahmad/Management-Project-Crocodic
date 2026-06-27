<?php

namespace App\Support;

use App\Models\Administration;
use App\Models\NotificationRead;
use App\Models\Project;
use App\Models\Task;
use App\Models\TaskOwnershipTransferRequest;
use App\Models\User;
use Illuminate\Support\Collection;

class DashboardNonAdminNotifications
{
    private const FEED_LIMIT = 35;

    private const QUERY_LIMIT = 25;

    /**
     * Feed kanan dashboard (director/user): izin pending (milik user) + project completed (30 hari), sama pola dengan admin.
     *
     * @return array{dashboardNotifications: Collection, dashboardNotificationBadgeCount: int}
     */
    public static function feed(User $user): array
    {
        $user->loadMissing('role:id,role');
        $role = $user->role?->role;

        $administrationNotifications = self::administrationNotifications($user);
        $reviewTasks = collect();
        $ownershipPendingRequests = collect();
        $projectTimelineAlerts = collect();

        if ($role === 'director') {
            $directorProjectIds = self::directorProjectIds($user->id);
            $reviewTasks = self::directorReviewTasks($user->id, $directorProjectIds);
            $ownershipPendingRequests = self::directorOwnershipPendingRequests($directorProjectIds);
            $projectTimelineAlerts = self::directorProjectTimelineAlerts($user->id);
        }

        $deadlineAlertTasks = self::deadlineAlertTasks($user->id);

        $dashboardNotifications = self::assembleFeed(
            $administrationNotifications,
            $reviewTasks,
            $ownershipPendingRequests,
            $deadlineAlertTasks,
            $projectTimelineAlerts
        );

        $readKeys = NotificationRead::query()
            ->where('id_user', $user->id)
            ->whereIn('notification_key', $dashboardNotifications->pluck('read_key')->filter()->all())
            ->pluck('notification_key')
            ->all();

        $readLookup = array_fill_keys($readKeys, true);
        $dashboardNotifications = $dashboardNotifications
            ->reject(fn ($note) => isset($readLookup[$note->read_key ?? '']))
            ->sortByDesc(fn ($n) => $n->sort_at?->timestamp ?? 0)
            ->take(self::FEED_LIMIT)
            ->values();

        return [
            'dashboardNotifications' => $dashboardNotifications,
            'dashboardNotificationBadgeCount' => $dashboardNotifications->count(),
        ];
    }

    /** @return Collection<int, Administration> */
    private static function administrationNotifications(User $user): Collection
    {
        $acceptStatusId = DashboardReferenceData::administrationStatusId('accept');
        $rejectStatusId = DashboardReferenceData::administrationStatusId('reject');
        $statusIds = array_values(array_filter([$acceptStatusId, $rejectStatusId]));

        return Administration::query()
            ->with([
                'user:id,name,id_divisi',
                'user.division:id,divisi',
                'category:id,name',
                'status:id,name',
            ])
            ->where('id_user', $user->id)
            ->when($statusIds !== [], fn ($query) => $query->whereIn('id_status', $statusIds))
            ->orderByDesc('updated_at')
            ->limit(self::QUERY_LIMIT)
            ->get([
                'id',
                'id_user',
                'id_category',
                'id_status',
                'start_date',
                'end_date',
                'created_at',
                'updated_at',
            ]);
    }

    /** @return list<string> */
    private static function directorProjectIds(string $directorId): array
    {
        return Project::query()
            ->where('id_director', $directorId)
            ->pluck('id')
            ->all();
    }

    /** @return Collection<int, Task> */
    private static function directorReviewTasks(string $directorId, array $projectIds): Collection
    {
        $reviewStatusIds = DashboardReferenceData::reviewStatusIds();
        if ($reviewStatusIds === [] || $projectIds === []) {
            return collect();
        }

        return Task::query()
            ->with([
                'project:id,name',
                'status:id,status,class',
                'user:id,name,id_divisi',
                'user.division:id,divisi',
            ])
            ->whereIn('id_status', $reviewStatusIds)
            ->whereIn('id_project', $projectIds)
            ->where('id_user', '!=', $directorId)
            ->orderByDesc('running_review_at')
            ->orderByDesc('updated_at')
            ->limit(self::QUERY_LIMIT)
            ->get([
                'id',
                'name',
                'id_user',
                'id_project',
                'id_status',
                'running_review_at',
                'updated_at',
            ]);
    }

    /** @return Collection<int, TaskOwnershipTransferRequest> */
    private static function directorOwnershipPendingRequests(array $projectIds): Collection
    {
        if ($projectIds === []) {
            return collect();
        }

        return TaskOwnershipTransferRequest::query()
            ->with([
                'task:id,name,id_project,id_status',
                'task.project:id,name',
                'task.status:id,status,class',
                'requestedBy:id,name',
                'toUser:id,name',
            ])
            ->where('status', TaskOwnershipTransferRequest::STATUS_PENDING)
            ->whereHas('task', fn ($q) => $q->whereIn('id_project', $projectIds))
            ->orderByDesc('created_at')
            ->limit(self::QUERY_LIMIT)
            ->get();
    }

    /** @return Collection<int, object> */
    private static function deadlineAlertTasks(string $userId): Collection
    {
        $statusIds = DashboardReferenceData::progressAndRevisionStatusIds();
        if ($statusIds === []) {
            return collect();
        }

        $deadlineCutoff = now()->addSeconds(1800);

        return Task::query()
            ->with([
                'project:id,name',
                'status:id,status,class',
                'difficulty:id,difficulty,class',
            ])
            ->where('id_user', $userId)
            ->whereIn('id_status', $statusIds)
            ->where(function ($q) use ($deadlineCutoff) {
                $q->where(function ($revision) use ($deadlineCutoff) {
                    $revision->whereNotNull('revision_deadline_at')
                        ->where('revision_deadline_at', '<=', $deadlineCutoff);
                })->orWhere(function ($progress) {
                    $progress->whereNotNull('running_started_at');
                });
            })
            ->orderByDesc('updated_at')
            ->limit(self::QUERY_LIMIT)
            ->get([
                'id',
                'name',
                'id_user',
                'id_project',
                'id_status',
                'id_difficulty',
                'running_started_at',
                'revision_deadline_at',
                'created_at',
                'updated_at',
            ])
            ->map(function (Task $task) {
                $deadline = TaskRunningTimer::deadlineFor($task);
                if (! $deadline) {
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
    }

    /** @return Collection<int, object> */
    private static function directorProjectTimelineAlerts(string $directorId): Collection
    {
        $statusIds = DashboardReferenceData::todoRunningProjectStatusIds();
        if ($statusIds === []) {
            return collect();
        }

        $windowEnd = now()->addDay()->toDateString();

        return Project::query()
            ->with(['status:id,status,class'])
            ->where('id_director', $directorId)
            ->whereIn('id_status', $statusIds)
            ->where(function ($q) use ($windowEnd) {
                $q->where(function ($todo) use ($windowEnd) {
                    $todo->whereDate('start_date', '<=', $windowEnd);
                })->orWhere(function ($running) use ($windowEnd) {
                    $running->whereDate('end_date', '<=', $windowEnd);
                });
            })
            ->orderByDesc('updated_at')
            ->limit(self::QUERY_LIMIT)
            ->get([
                'id',
                'name',
                'id_status',
                'id_director',
                'start_date',
                'end_date',
                'updated_at',
            ])
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

    /**
     * @param  Collection<int, Administration>  $administrationNotifications
     * @param  Collection<int, Task>  $reviewTasks
     * @param  Collection<int, TaskOwnershipTransferRequest>  $ownershipPendingRequests
     * @param  Collection<int, object>  $deadlineAlertTasks
     * @param  Collection<int, object>  $projectTimelineAlerts
     */
    private static function assembleFeed(
        Collection $administrationNotifications,
        Collection $reviewTasks,
        Collection $ownershipPendingRequests,
        Collection $deadlineAlertTasks,
        Collection $projectTimelineAlerts
    ): Collection {
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

        foreach ($ownershipPendingRequests as $ownershipRequest) {
            $dashboardNotifications->push((object) [
                'kind' => 'task_ownership_pending',
                'sort_at' => $ownershipRequest->created_at,
                'ownership_request' => $ownershipRequest,
                'read_key' => self::buildReadKey(
                    'task_ownership_pending',
                    (string) $ownershipRequest->id,
                    'pending',
                    $ownershipRequest->created_at
                ),
            ]);
        }

        foreach ($deadlineAlertTasks as $deadlineAlert) {
            $dashboardNotifications->push((object) [
                'kind' => 'task_deadline_alert',
                'sort_at' => $deadlineAlert->sort_at,
                'task' => $deadlineAlert->task,
                'balance_seconds' => $deadlineAlert->balance_seconds,
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
                'read_key' => null,
            ]);
        }

        return $dashboardNotifications;
    }

    private static function buildReadKey(string $kind, string $entityId, string $state, $at): string
    {
        $timestamp = $at?->timestamp ?? 0;

        return implode(':', [$kind, $entityId, $state, (string) $timestamp]);
    }
}
