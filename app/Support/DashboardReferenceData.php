<?php

namespace App\Support;

use App\Models\Role;
use App\Models\StatusAdministration;
use App\Models\Statussdm;
use App\Models\StatusTask;
use App\Models\TaskDifficulty;
use Illuminate\Support\Facades\Cache;

class DashboardReferenceData
{
    public static function administrationStatusId(string $name): ?string
    {
        return Cache::remember("dashboard:admin_status:{$name}", 3600, function () use ($name) {
            return StatusAdministration::query()->where('name', $name)->value('id');
        });
    }

    /**
     * @return array<string, list<string>>
     */
    public static function sdmActivityStatusIdGroups(): array
    {
        return Cache::remember('dashboard:sdm_activity_status_id_groups', 3600, function () {
            $groups = [
                'Not Ready' => [],
                'Stand By' => [],
            ];

            Statussdm::query()
                ->whereIn('status_sdm', array_keys($groups))
                ->orderBy('created_at')
                ->get(['id', 'status_sdm'])
                ->each(function (Statussdm $row) use (&$groups) {
                    $groups[$row->status_sdm][] = $row->id;
                });

            return $groups;
        });
    }

    /**
     * @deprecated Use sdmActivityStatusIdGroups() so duplicate status rows still match.
     *
     * @return array<string, string|null>
     */
    public static function sdmActivityStatusIds(): array
    {
        $groups = self::sdmActivityStatusIdGroups();

        return [
            'Not Ready' => $groups['Not Ready'][0] ?? null,
            'Stand By' => $groups['Stand By'][0] ?? null,
        ];
    }

    /** @return list<string> */
    public static function staffDirectorRoleIds(): array
    {
        return Cache::remember('dashboard:staff_director_role_ids', 3600, function () {
            return Role::query()
                ->whereIn('role', ['staff', 'director'])
                ->pluck('id')
                ->all();
        });
    }

    /** @return list<string> */
    public static function activeWorkStatusIds(): array
    {
        return Cache::remember('dashboard:active_work_status_ids', 3600, function () {
            return StatusTask::query()
                ->where(function ($q) {
                    $q->whereIn('class', ['todo', 'progress', 'review', 'revision'])
                        ->orWhereIn('status', ['To Do', 'In progress', 'Review', 'Revision']);
                })
                ->pluck('id')
                ->all();
        });
    }

    /** @return list<string> */
    public static function completeStatusIds(): array
    {
        return Cache::remember('dashboard:complete_status_ids', 3600, function () {
            return StatusTask::query()
                ->where(function ($q) {
                    $q->where('class', 'complete')
                        ->orWhere('status', 'Complete');
                })
                ->pluck('id')
                ->all();
        });
    }

    public static function standByTaskDifficultyId(): ?string
    {
        return Cache::remember('dashboard:standby_difficulty_id', 3600, function () {
            return TaskDifficulty::query()->where('difficulty', 'Stand By')->value('id');
        });
    }

    /** @return list<string> */
    public static function progressAndRevisionStatusIds(): array
    {
        return Cache::remember('dashboard:progress_revision_status_ids', 3600, function () {
            return StatusTask::query()
                ->where(function ($q) {
                    $q->whereIn('class', ['progress', 'revision'])
                        ->orWhereIn('status', ['In progress', 'Revision']);
                })
                ->pluck('id')
                ->all();
        });
    }

    /** @return list<string> */
    public static function reviewStatusIds(): array
    {
        return Cache::remember('dashboard:review_status_ids', 3600, function () {
            return StatusTask::query()
                ->where(function ($q) {
                    $q->where('class', 'review')
                        ->orWhere('status', 'Review');
                })
                ->pluck('id')
                ->all();
        });
    }

    /** @return list<string> */
    public static function todoRunningProjectStatusIds(): array
    {
        return Cache::remember('dashboard:todo_running_project_status_ids', 3600, function () {
            return \App\Models\StatusProject::query()
                ->where(function ($q) {
                    $q->whereIn('class', ['todo', 'running'])
                        ->orWhereIn('status', ['To Do', 'Running']);
                })
                ->pluck('id')
                ->all();
        });
    }
}
