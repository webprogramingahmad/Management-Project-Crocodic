<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('task_ownership_transfers', function (Blueprint $table) {
            $table->boolean('was_overdue_at_transfer')->default(false)->after('task_status_at_transfer');
            $table->timestamp('timer_reset_at')->nullable()->after('was_overdue_at_transfer');
            $table->timestamp('previous_running_started_at')->nullable()->after('timer_reset_at');
            $table->timestamp('previous_revision_deadline_at')->nullable()->after('previous_running_started_at');
        });
    }

    public function down(): void
    {
        Schema::table('task_ownership_transfers', function (Blueprint $table) {
            $table->dropColumn([
                'was_overdue_at_transfer',
                'timer_reset_at',
                'previous_running_started_at',
                'previous_revision_deadline_at',
            ]);
        });
    }
};
