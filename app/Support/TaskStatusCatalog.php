<?php

namespace App\Support;

use App\Models\StatusTask;

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
        return [
            self::TODO => StatusTask::firstByClass(self::TODO),
            self::PROGRESS => StatusTask::firstByClass(self::PROGRESS),
            self::REVIEW => StatusTask::firstByClass(self::REVIEW),
            self::REVISION => StatusTask::firstByClass(self::REVISION),
            self::COMPLETE => StatusTask::firstByClass(self::COMPLETE),
        ];
    }
}

