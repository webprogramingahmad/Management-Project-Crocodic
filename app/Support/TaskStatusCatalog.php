<?php

namespace App\Support;

use App\Models\StatusTask;
use Illuminate\Support\Facades\Cache;

class TaskStatusCatalog
{
    public const TODO = 'todo';
    public const PROGRESS = 'progress';
    public const REVIEW = 'review';
    public const REVISION = 'revision';
    public const COMPLETE = 'complete';

    /**
     * @return array<string, ?StatusTask>
     */
    public static function mapByClass(): array
    {
        return Cache::remember('task_status_catalog_map', 3600, function () {
            $statuses = StatusTask::query()->get();

            $resolve = function (string $class) use ($statuses): ?StatusTask {
                $labels = StatusTask::legacyLabelsForClass($class);

                return $statuses->first(function (StatusTask $status) use ($class, $labels) {
                    if (strtolower((string) ($status->class ?? '')) === strtolower($class)) {
                        return true;
                    }

                    return in_array((string) ($status->status ?? ''), $labels, true);
                });
            };

            return [
                self::TODO => $resolve(self::TODO),
                self::PROGRESS => $resolve(self::PROGRESS),
                self::REVIEW => $resolve(self::REVIEW),
                self::REVISION => $resolve(self::REVISION),
                self::COMPLETE => $resolve(self::COMPLETE),
            ];
        });
    }
}

