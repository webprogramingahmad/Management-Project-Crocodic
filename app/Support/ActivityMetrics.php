<?php

namespace App\Support;

use App\Models\StatusAdministration;
use App\Models\StatusTask;
use App\Models\User;
use Carbon\Carbon;

class ActivityMetrics
{
    /** Relasi yang dibutuhkan untuk menghitung metrik activity. */
    public const EAGER_RELATIONS = [
        'administrations',
        'tasks.difficulty',
        'tasks.project',
        'projects.status',
        'directedProjects.status',
        'division',
        'role',
    ];

    /**
     * Lengkapi sebuah user dengan atribut metrik activity untuk periode tertentu.
     */
    public static function decorate(
        User $user,
        int $month,
        int $year,
        ?StatusAdministration $acceptedStatus = null,
        ?StatusTask $completeStatus = null
    ): User {
        $acceptedStatus ??= StatusAdministration::where('name', 'accept')->first();
        $completeStatus ??= StatusTask::firstByClass('complete');

        $user->accepted_absent_count = $user->administrations
            ->where('id_status', $acceptedStatus?->id)
            ->filter(function ($item) use ($month, $year) {
                return Carbon::parse($item->start_date)->month == $month &&
                    Carbon::parse($item->start_date)->year == $year;
            })
            ->count();

        $user->completed_task_count = $user->tasks
            ->where('id_status', $completeStatus?->id)
            ->filter(function ($item) use ($month, $year) {
                return Carbon::parse($item->updated_at)->month == $month &&
                    Carbon::parse($item->updated_at)->year == $year;
            })
            ->count();

        $periodProjects = $user->projects
            ->concat($user->directedProjects)
            ->unique('id')
            ->filter(function ($project) use ($month, $year) {
                return Carbon::parse($project->start_date)->year <= $year &&
                    Carbon::parse($project->end_date)->year >= $year &&
                    Carbon::parse($project->start_date)->month <= $month &&
                    Carbon::parse($project->end_date)->month >= $month;
            })
            ->values();
        $user->period_projects = $periodProjects;
        $user->projects_joined_count = $periodProjects->count();

        // Time Performance: persentase task COMPLETE yang diselesaikan tepat waktu pada periode terpilih.
        // Basis sama dengan "Tasks done" (status complete + updated_at bulan terpilih),
        // ketepatan waktu dinilai dari running_review_at vs deadline level task.
        // Task tanpa data deadline (reviewedOnTime null) & difficulty "Stand By" dikecualikan.
        $completedTasks = $user->tasks
            ->filter(function ($task) use ($completeStatus, $month, $year) {
                if ((string) $task->id_status !== (string) $completeStatus?->id) {
                    return false;
                }

                if (strcasecmp((string) ($task->difficulty?->difficulty ?? ''), 'Stand By') === 0) {
                    return false;
                }

                $completedAt = Carbon::parse($task->updated_at);
                if ($completedAt->month != $month || $completedAt->year != $year) {
                    return false;
                }

                return TaskRunningTimer::reviewedOnTime($task) !== null;
            });

        $onTimeTasks = $completedTasks
            ->filter(fn ($task) => TaskRunningTimer::reviewedOnTime($task) === true)
            ->values();
        $lateTasks = $completedTasks
            ->filter(fn ($task) => TaskRunningTimer::reviewedOnTime($task) === false)
            ->values();

        $completedTotal = $completedTasks->count();
        $onTimeCount = $onTimeTasks->count();

        $user->completed_ontime_tasks = $onTimeTasks;
        $user->completed_late_tasks = $lateTasks;

        $user->time_performance_total = $completedTotal;
        $user->time_performance_ontime = $onTimeCount;
        $user->time_performance = $completedTotal > 0
            ? (int) round($onTimeCount / $completedTotal * 100)
            : 0;

        return $user;
    }
}
