<?php

namespace App\Support;

use App\Models\Project;
use App\Models\Task;

class ProjectReportBuilder
{
    /**
     * @return array<string, mixed>
     */
    public static function build(Project $project, bool $forPdf = false): array
    {
        $statusMap = TaskStatusCatalog::mapByClass();
        $completeStatusId = (string) ($statusMap[TaskStatusCatalog::COMPLETE]?->id ?? '');

        $taskQuery = Task::with(['user.division', 'user.role', 'creator', 'difficulty', 'status'])
            ->where('id_project', $project->id)
            ->excludingStandByDifficulty()
            ->orderBy('created_at', 'desc');

        if ($forPdf) {
            $tasks = $taskQuery
                ->with(['photos', 'revisionCycles'])
                ->get();
        } else {
            $tasks = $taskQuery->withCount('photos')->get();
        }

        $countByClass = [
            'todo' => 0,
            'progress' => 0,
            'review' => 0,
            'revision' => 0,
            'complete' => 0,
        ];

        foreach ($tasks as $task) {
            $class = strtolower((string) ($task->status?->class ?? ''));
            if (isset($countByClass[$class])) {
                $countByClass[$class]++;
            }
        }

        $totalTasks = $tasks->count();
        $completedCount = $countByClass['complete'];
        $activeCount = $countByClass['progress'] + $countByClass['review'] + $countByClass['revision'];
        $progressPercent = $totalTasks > 0
            ? (int) round($completedCount / $totalTasks * 100)
            : 0;

        $completedForTiming = $tasks->filter(function ($task) use ($completeStatusId) {
            return (string) $task->id_status === $completeStatusId
                && TaskRunningTimer::reviewedOnTime($task) !== null;
        });
        $onTimeCount = $completedForTiming
            ->filter(fn ($task) => TaskRunningTimer::reviewedOnTime($task) === true)
            ->count();
        $onTimePercent = $completedForTiming->count() > 0
            ? (int) round($onTimeCount / $completedForTiming->count() * 100)
            : 0;

        $memberStats = $tasks
            ->groupBy('id_user')
            ->map(function ($userTasks) use ($completeStatusId) {
                $user = $userTasks->first()?->user;
                $completed = $userTasks->filter(
                    fn ($t) => (string) $t->id_status === $completeStatusId
                );
                $completedTimed = $completed->filter(
                    fn ($t) => TaskRunningTimer::reviewedOnTime($t) !== null
                );

                return (object) [
                    'user' => $user,
                    'total' => $userTasks->count(),
                    'completed' => $completed->count(),
                    'active' => $userTasks->count() - $completed->count(),
                    'on_time' => $completedTimed->filter(
                        fn ($t) => TaskRunningTimer::reviewedOnTime($t) === true
                    )->count(),
                    'late' => $completedTimed->filter(
                        fn ($t) => TaskRunningTimer::reviewedOnTime($t) === false
                    )->count(),
                ];
            })
            ->sortByDesc('total')
            ->values();

        return compact(
            'project',
            'tasks',
            'totalTasks',
            'completedCount',
            'activeCount',
            'countByClass',
            'progressPercent',
            'onTimePercent',
            'memberStats',
        );
    }
}
