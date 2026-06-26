<?php

namespace App\Support;

use App\Models\Project;
use App\Models\TaskDifficulty;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class TaskBoardReferenceData
{
    public static function difficultiesForForms(): Collection
    {
        return Cache::remember('task_board:difficulties', 3600, function () {
            return TaskDifficulty::query()
                ->oldest()
                ->where('difficulty', '!=', 'Stand By')
                ->get();
        });
    }

    /**
     * @param  Collection<int, Project>|iterable<Project>  $projects
     */
    public static function decorateProjectsWithAbsentLabels(iterable $projects): void
    {
        foreach ($projects as $project) {
            if (! $project->relationLoaded('sdms')) {
                continue;
            }
            foreach ($project->sdms as $sdm) {
                $activeAdm = $sdm->administrations->first();
                if (! $activeAdm || ! $activeAdm->end_date) {
                    continue;
                }
                $returnDate = Carbon::parse($activeAdm->end_date)->addDay();
                $sdm->is_absent_now = true;
                $sdm->absent_returns_on_label = $returnDate->translatedFormat('j M');
            }
        }
    }
}
