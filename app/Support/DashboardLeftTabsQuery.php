<?php

namespace App\Support;

use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;

class DashboardLeftTabsQuery
{
    private const CACHE_SECONDS = 90;

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
        return [
            'ready' => Cache::remember(
                "dashboard:tab:ready:{$today}",
                self::CACHE_SECONDS,
                fn () => self::readyTab($today)
            ),
            'notready' => Cache::remember(
                "dashboard:tab:notready:{$today}",
                self::CACHE_SECONDS,
                fn () => self::notReadyTab($today)
            ),
            'standby' => Cache::remember(
                "dashboard:tab:standby:{$today}",
                self::CACHE_SECONDS,
                fn () => self::standByTab($today)
            ),
            'absent' => Cache::remember(
                "dashboard:tab:absent:{$today}",
                self::CACHE_SECONDS,
                fn () => self::absentTab($today)
            ),
            'complete' => Cache::remember(
                "dashboard:tab:complete:{$today}",
                self::CACHE_SECONDS,
                fn () => self::completeTab($today)
            ),
        ];
    }

    /** @return \Illuminate\Database\Eloquent\Collection<int, Task> */
    private static function readyTab(string $today): \Illuminate\Database\Eloquent\Collection
    {
        $activeStatusIds = DashboardReferenceData::activeWorkStatusIds();
        if ($activeStatusIds === []) {
            return Task::query()->whereRaw('1 = 0')->get();
        }

        $absentUserIds = DashboardAbsenceUsers::idsFor($today);

        return Task::with([
            'project:id,name',
            'status:id,status,class',
            'difficulty:id,difficulty,class',
            'user:id,name,avatar,id_divisi,id_role',
            'user.division:id,divisi',
            'user.role:id,role',
        ])
            ->whereIn('id_status', $activeStatusIds)
            ->when($absentUserIds !== [], fn (Builder $q) => $q->whereNotIn('id_user', $absentUserIds))
            ->tap(fn (Builder $q) => self::applyStandByDifficultyFilter($q))
            ->orderByDesc('created_at')
            ->get([
                'tasks.id',
                'tasks.name',
                'tasks.id_user',
                'tasks.id_project',
                'tasks.id_status',
                'tasks.id_difficulty',
                'tasks.created_at',
            ]);
    }

    /** @return \Illuminate\Database\Eloquent\Collection<int, User> */
    private static function notReadyTab(string $today): \Illuminate\Database\Eloquent\Collection
    {
        return self::operationalUsersByActivityStatus($today, DashboardReferenceData::sdmActivityStatusIdGroups()['Not Ready'] ?? []);
    }

    /** @return \Illuminate\Database\Eloquent\Collection<int, User> */
    private static function standByTab(string $today): \Illuminate\Database\Eloquent\Collection
    {
        return self::operationalUsersByActivityStatus($today, DashboardReferenceData::sdmActivityStatusIdGroups()['Stand By'] ?? []);
    }

    /**
     * @param  list<string>  $activityStatusIds
     * @return \Illuminate\Database\Eloquent\Collection<int, User>
     */
    private static function operationalUsersByActivityStatus(string $today, array $activityStatusIds): \Illuminate\Database\Eloquent\Collection
    {
        if ($activityStatusIds === []) {
            return User::query()->whereRaw('1 = 0')->get();
        }

        $roleIds = DashboardReferenceData::staffDirectorRoleIds();
        if ($roleIds === []) {
            return User::query()->whereRaw('1 = 0')->get();
        }

        $absentUserIds = DashboardAbsenceUsers::idsFor($today);

        return User::with(['division:id,divisi', 'role:id,role'])
            ->select('users.id', 'users.name', 'users.avatar', 'users.id_divisi', 'users.id_role', 'users.id_activity_status_sdm')
            ->whereIn('id_role', $roleIds)
            ->whereIn('id_activity_status_sdm', $activityStatusIds)
            ->when($absentUserIds !== [], fn (Builder $q) => $q->whereNotIn('users.id', $absentUserIds))
            ->orderBy('name')
            ->get();
    }

    /** @return \Illuminate\Database\Eloquent\Collection<int, User> */
    private static function absentTab(string $today): \Illuminate\Database\Eloquent\Collection
    {
        $absentUserIds = DashboardAbsenceUsers::idsFor($today);
        if ($absentUserIds === []) {
            return User::query()->whereRaw('1 = 0')->get();
        }

        $acceptedStatusId = DashboardReferenceData::administrationStatusId('accept');

        return User::query()
            ->select('users.id', 'users.name', 'users.avatar', 'users.id_divisi')
            ->whereIn('users.id', $absentUserIds)
            ->with([
                'administrations' => function ($q) use ($today, $acceptedStatusId) {
                    $q->select('id', 'id_user', 'start_date', 'end_date', 'id_status', 'id_category')
                        ->whereDate('start_date', '<=', $today)
                        ->whereDate('end_date', '>=', $today);
                    if ($acceptedStatusId) {
                        $q->where('id_status', $acceptedStatusId);
                    } else {
                        $q->whereRaw('1 = 0');
                    }
                },
                'administrations.status:id,name',
                'administrations.category:id,name',
            ])
            ->orderBy('name')
            ->get();
    }

    /** @return \Illuminate\Database\Eloquent\Collection<int, Task> */
    private static function completeTab(string $today): \Illuminate\Database\Eloquent\Collection
    {
        $completeStatusIds = DashboardReferenceData::completeStatusIds();
        if ($completeStatusIds === []) {
            return Task::query()->whereRaw('1 = 0')->get();
        }

        return Task::with([
            'project:id,name',
            'difficulty:id,difficulty,class',
            'status:id,status,class',
            'user:id,name,avatar,id_divisi,id_role',
            'user.division:id,divisi',
            'user.role:id,role',
        ])
            ->whereIn('id_status', $completeStatusIds)
            ->whereDate('tasks.updated_at', $today)
            ->tap(fn (Builder $q) => self::applyStandByDifficultyFilter($q))
            ->orderByDesc('tasks.updated_at')
            ->get([
                'tasks.id',
                'tasks.name',
                'tasks.id_user',
                'tasks.id_project',
                'tasks.id_status',
                'tasks.id_difficulty',
                'tasks.updated_at',
            ]);
    }

    private static function applyStandByDifficultyFilter(Builder $query): void
    {
        $standById = DashboardReferenceData::standByTaskDifficultyId();
        if (! $standById) {
            return;
        }

        $query->where(function (Builder $q) use ($standById) {
            $q->whereNull('tasks.id_difficulty')
                ->orWhere('tasks.id_difficulty', '!=', $standById);
        });
    }
}
