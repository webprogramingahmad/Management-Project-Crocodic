<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            if (!Schema::hasColumn('tasks', 'running_started_at')) {
                $table->timestamp('running_started_at')->nullable()->after('updated_at');
            }
        });

        $progressId = DB::table('status_tasks')->where('class', 'progress')->value('id');
        if ($progressId) {
            $ids = DB::table('tasks')
                ->where('id_status', $progressId)
                ->whereNull('running_started_at')
                ->pluck('id');
            foreach ($ids as $taskId) {
                $row = DB::table('tasks')->where('id', $taskId)->first();
                if (!$row) {
                    continue;
                }
                $ts = $row->updated_at ?? $row->created_at ?? now();
                DB::table('tasks')->where('id', $taskId)->update(['running_started_at' => $ts]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            if (Schema::hasColumn('tasks', 'running_started_at')) {
                $table->dropColumn('running_started_at');
            }
        });
    }
};
