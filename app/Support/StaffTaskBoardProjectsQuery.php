<?php

namespace App\Support;

use App\Models\Project;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class StaffTaskBoardProjectsQuery
{
    /**
     * Project + SDM yang dibutuhkan board task staff (termasuk label absent).
     *
     * @return Collection<int, Project>
     */
    public static function forUser(string $userId, string $today): Collection
    {
        return Cache::remember(
            "staff_task_board:projects:{$userId}:{$today}",
            30,
            fn () => self::loadForUser($userId, $today)
        );
    }

    /**
     * @return Collection<int, Project>
     */
    private static function loadForUser(string $userId, string $today): Collection
    {
        return Project::query()
            ->whereHas('sdms', function ($q) use ($userId) {
                $q->where('users.id', $userId);
            })
            ->with([
                'status:id,status,class',
                'sdms' => function ($q) use ($today) {
                    $q->select('users.id', 'users.name', 'users.avatar')
                        ->with(['administrations' => function ($aq) use ($today) {
                            $aq->select('id', 'id_user', 'end_date')
                                ->whereDate('start_date', '<=', $today)
                                ->whereDate('end_date', '>=', $today)
                                ->whereHas('status', function ($sq) {
                                    $sq->where('name', 'accept');
                                })
                                ->orderByDesc('end_date');
                        }]);
                },
            ])
            ->orderBy('name')
            ->get([
                'projects.id',
                'projects.name',
                'projects.description',
                'projects.id_status',
                'projects.start_date',
                'projects.end_date',
            ]);
    }
}
