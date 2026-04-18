<?php

namespace App\Support;

use App\Models\Project;
use App\Models\StatusProject;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ProjectWorkload
{
    /**
     * Status project yang dihitung: To do & Running (by `class` di status_projects).
     *
     * @return list<string>
     */
    public static function activeStatusIds(): array
    {
        return StatusProject::query()
            ->whereIn('class', ['todo', 'running'])
            ->pluck('id')
            ->all();
    }

    /**
     * Untuk tiap user id: jumlah project unik (sebagai director dan/atau SDM) +
     * sisa hari ke deadline terjauh (max end_date di antara project tersebut).
     *
     * @param  list<string>  $userIds
     * @return array<string, array{count: int, max_days: int}>
     */
    public static function statsMapForUserIds(array $userIds): array
    {
        $userIds = array_values(array_unique(array_filter($userIds)));
        $empty = ['count' => 0, 'max_days' => 0];
        $out = array_fill_keys($userIds, $empty);

        $statusIds = self::activeStatusIds();
        if ($statusIds === [] || $userIds === []) {
            return $out;
        }

        $projects = Project::query()
            ->whereIn('id_status', $statusIds)
            ->with(['sdms:id'])
            ->get(['id', 'id_director', 'end_date']);

        /** @var array<string, list<string>> $userProjectIds */
        $userProjectIds = array_fill_keys($userIds, []);

        foreach ($projects as $project) {
            $involved = collect([$project->id_director])
                ->merge($project->sdms->pluck('id'))
                ->filter()
                ->unique();

            foreach ($involved as $uid) {
                if (! array_key_exists($uid, $out)) {
                    continue;
                }
                if (! in_array($project->id, $userProjectIds[$uid], true)) {
                    $userProjectIds[$uid][] = $project->id;
                }
            }
        }

        $today = Carbon::today();

        foreach ($userIds as $uid) {
            $pids = $userProjectIds[$uid];
            if ($pids === []) {
                continue;
            }

            /** @var Collection<int, Project> $subset */
            $subset = $projects->whereIn('id', $pids);
            $count = $subset->count();
            $latestEnd = $subset->max('end_date');

            $maxDays = 0;
            if ($latestEnd !== null) {
                $end = Carbon::parse($latestEnd)->startOfDay();
                $maxDays = $end->lt($today) ? 0 : (int) $today->diffInDays($end, false);
            }

            $out[$uid] = ['count' => $count, 'max_days' => $maxDays];
        }

        return $out;
    }
}
