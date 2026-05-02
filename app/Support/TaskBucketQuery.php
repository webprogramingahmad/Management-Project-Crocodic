<?php

namespace App\Support;

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
            'project:id,name',
            'difficulty:id,difficulty,class',
            'status:id,status,class',
            'user:id,name,avatar',
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public static function forUserByStatusClass(string $userId, string $statusClass, array $filters = []): Collection
    {
        $projectId = $filters['project_id'] ?? null;
        $date = $filters['date'] ?? null;
        $dateColumn = $filters['date_column'] ?? 'tasks.created_at';
        $defaultDate = $filters['default_date'] ?? null;

        return Task::query()
            ->select([
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
            ])
            ->with(self::boardRelations())
            ->where('id_user', $userId)
            ->excludingStandByDifficulty()
            ->whereHas('status', fn ($q) => $q->whereClassOrLegacy($statusClass))
            ->when($projectId, fn ($q) => $q->where('tasks.id_project', $projectId))
            ->when(
                $date !== null || $defaultDate !== null,
                fn ($q) => $q->when(
                    $date,
                    fn ($qq) => $qq->whereDate($dateColumn, $date),
                    fn ($qq) => $defaultDate ? $qq->whereDate($dateColumn, $defaultDate) : $qq
                )
            )
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
        $dateColumn = $filters['date_column'] ?? 'tasks.created_at';
        $defaultDate = $filters['default_date'] ?? null;

        return (clone $baseQuery)
            ->select([
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
            ])
            ->with(self::boardRelations())
            ->whereHas('status', fn ($q) => $q->whereClassOrLegacy($statusClass))
            ->when($projectId, fn ($q) => $q->where('tasks.id_project', $projectId))
            ->when(
                $date !== null || $defaultDate !== null,
                fn ($q) => $q->when(
                    $date,
                    fn ($qq) => $qq->whereDate($dateColumn, $date),
                    fn ($qq) => $defaultDate ? $qq->whereDate($dateColumn, $defaultDate) : $qq
                )
            )
            ->get();
    }
}

