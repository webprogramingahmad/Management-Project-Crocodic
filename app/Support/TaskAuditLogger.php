<?php

namespace App\Support;

use Illuminate\Support\Facades\Log;

class TaskAuditLogger
{
    /**
     * @param  array<string, mixed>  $context
     */
    public static function info(string $event, array $context): void
    {
        Log::info($event, $context);
    }

    /**
     * @param  array<string, mixed>  $context
     */
    public static function warning(string $event, array $context): void
    {
        Log::warning($event, $context);
    }
}
