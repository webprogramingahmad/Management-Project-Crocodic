<?php

namespace App\Support;

use App\Models\StatusTask;
use App\Models\Task;
use Illuminate\Database\Eloquent\Collection;

class TaskBucketQuery
{
    /**
     * Relasi minimum yang dibutuhkan card task di board.
     *
     * @return array<int, string>
     */
    private static function boardRelations(): array
    {
        return [
            'project:id,name,id_director',
            'difficulty:id,difficulty,class',
            'status:id,status,class',
            'user:id,name,avatar',
            'photos',
            'submissions:id,id_task',
            'revisionCycles:id,id_task,cycle_number,notes,links,revision_hours,entered_revision_at,deadline_at',
            'pendingOwnershipTransferRequest.toUser:id,name',
            'pendingOwnershipTransferRequest.requestedBy:id,name',
        ];
    }

    /**
     * @return array<int, string>
     */
    private static function boardColumns(): array
    {
        return [
            'tasks.id',
            'tasks.name',
            'tasks.description',
            'tasks.id_user',
            'tasks.id_project',
            'tasks.id_difficulty',
            'tasks.id_status',
            'tasks.created_by',
            'tasks.created_at',
            'tasks.updated_at',
            'tasks.running_started_at',
            'tasks.running_review_at',
            'tasks.revision_deadline_at',
            'tasks.revision_hours',
        ];
    }

    /**
     * Muat kolom board staff dalam 2 query (work + complete), bukan 5 query terpisah.
     *
     * @param  array<string, mixed>  $bucketFilters
     * @param  array<string, mixed>  $completeDateFilters
     * @return array{todo: Collection, progress: Collection, review: Collection, revision: Collection, complete: Collection}
     */
    public static function forUserBoardColumns(string $userId, array $bucketFilters = [], array $completeDateFilters = []): array
    {
        $projectId = $bucketFilters['project_id'] ?? null;

        $workClasses = [
            TaskStatusCatalog::TODO,
            TaskStatusCatalog::PROGRESS,
            TaskStatusCatalog::REVIEW,
            TaskStatusCatalog::REVISION,
        ];

        $workTasks = self::baseUserTaskQuery($userId, $projectId)
            ->whereHas('status', function ($q) use ($workClasses) {
                $q->where(function ($w) use ($workClasses) {
                    foreach ($workClasses as $class) {
                        $w->orWhere(function ($sub) use ($class) {
                            $sub->whereClassOrLegacy($class);
                        });
                    }
                });
            })
            ->get();

        $columns = [];
        foreach ($workClasses as $class) {
            $columns[$class] = $workTasks
                ->filter(fn (Task $task) => self::taskMatchesStatusClass($task, $class))
                ->values();
        }

        $complete = self::forUserByStatusClass(
            $userId,
            TaskStatusCatalog::COMPLETE,
            array_merge($completeDateFilters, [
                'project_id' => $projectId,
                'date_column' => 'tasks.updated_at',
            ])
        );

        return [
            'todo' => $columns[TaskStatusCatalog::TODO],
            'progress' => $columns[TaskStatusCatalog::PROGRESS],
            'review' => $columns[TaskStatusCatalog::REVIEW],
            'revision' => $columns[TaskStatusCatalog::REVISION],
            'complete' => $complete,
        ];
    }

    private static function taskMatchesStatusClass(Task $task, string $class): bool
    {
        $status = $task->status;
        if (! $status) {
            return false;
        }

        if (strtolower((string) ($status->class ?? '')) === strtolower($class)) {
            return true;
        }

        return in_array(
            (string) ($status->status ?? ''),
            StatusTask::legacyLabelsForClass($class),
            true
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder<Task>
     */
    private static function baseUserTaskQuery(string $userId, ?string $projectId)
    {
        return Task::query()
            ->select(self::boardColumns())
            ->with(self::boardRelations())
            ->where('id_user', $userId)
            ->excludingStandByDifficulty()
            ->when($projectId, fn ($q) => $q->where('tasks.id_project', $projectId));
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public static function forUserByStatusClass(string $userId, string $statusClass, array $filters = []): Collection
    {
        $projectId = $filters['project_id'] ?? null;
        $date = $filters['date'] ?? null;
        $dateFrom = $filters['date_from'] ?? null;
        $dateTo = $filters['date_to'] ?? null;
        $dateColumn = $filters['date_column'] ?? 'tasks.created_at';
        $defaultDate = $filters['default_date'] ?? null;

        return self::baseUserTaskQuery($userId, $projectId)
            ->whereHas('status', fn ($q) => $q->whereClassOrLegacy($statusClass))
            ->when($date, fn ($q) => $q->whereDate($dateColumn, $date))
            ->when(! $date && $dateFrom, fn ($q) => $q->whereDate($dateColumn, '>=', $dateFrom))
            ->when(! $date && $dateTo, fn ($q) => $q->whereDate($dateColumn, '<=', $dateTo))
            ->when(! $date && ! $dateFrom && ! $dateTo && $defaultDate, fn ($q) => $q->whereDate($dateColumn, $defaultDate))
            ->get();
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder  $baseQuery
     * @param  array<string, mixed>  $filters
     */
    public static function forTaskQueryByStatusClass($baseQuery, string $statusClass, array $filters = []): Collection
    {
        $projectId = $filters['project_id'] ?? null;
        $date = $filters['date'] ?? null;
        $dateFrom = $filters['date_from'] ?? null;
        $dateTo = $filters['date_to'] ?? null;
        $dateColumn = $filters['date_column'] ?? 'tasks.created_at';
        $defaultDate = $filters['default_date'] ?? null;

        return (clone $baseQuery)
            ->select(self::boardColumns())
            ->with(self::boardRelations())
            ->whereHas('status', fn ($q) => $q->whereClassOrLegacy($statusClass))
            ->when($projectId, fn ($q) => $q->where('tasks.id_project', $projectId))
            ->when($date, fn ($q) => $q->whereDate($dateColumn, $date))
            ->when(! $date && $dateFrom, fn ($q) => $q->whereDate($dateColumn, '>=', $dateFrom))
            ->when(! $date && $dateTo, fn ($q) => $q->whereDate($dateColumn, '<=', $dateTo))
            ->when(! $date && ! $dateFrom && ! $dateTo && $defaultDate, fn ($q) => $q->whereDate($dateColumn, $defaultDate))
            ->get();
    }
}
