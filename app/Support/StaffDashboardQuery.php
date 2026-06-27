<?php

namespace App\Support;

use App\Models\Project;
use App\Models\Task;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;

class StaffDashboardQuery
{
    /**
     * Task aktif milik staff untuk panel kanan dashboard.
     *
     * @return Collection<int, Task>
     */
    public static function activeTasksForUser(string $userId): Collection
    {
        return Task::query()
            ->with([
                'project:id,name',
                'status:id,status,class',
                'difficulty:id,difficulty,class',
            ])
            ->where('id_user', $userId)
            ->excludingStandByDifficulty()
            ->whereHas('status', function ($q) {
                $q->whereIn('class', ['todo', 'progress', 'review', 'revision']);
            })
            ->orderByDesc('updated_at')
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

    /**
     * Project aktif staff untuk panel kanan dashboard.
     *
     * @return Collection<int, Project>
     */
    public static function activeProjectsForUser(string $userId, string $today): Collection
    {
        return Project::query()
            ->with([
                'status:id,status,class',
                'difficulty:id,difficulty,class',
                'sdms' => fn ($q) => $q->select('users.id', 'users.name', 'users.avatar'),
            ])
            ->whereHas('sdms', function ($q) use ($userId) {
                $q->where('users.id', $userId);
            })
            ->whereHas('status', function ($q) {
                $q->whereIn('class', ['todo', 'running']);
            })
            ->whereDate('end_date', '>=', $today)
            ->orderByDesc('created_at')
            ->get([
                'projects.id',
                'projects.name',
                'projects.description',
                'projects.id_status',
                'projects.id_difficulty',
                'projects.created_at',
            ]);
    }
}
