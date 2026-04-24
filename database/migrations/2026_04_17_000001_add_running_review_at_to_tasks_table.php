<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('tasks', 'running_review_at')) {
            Schema::table('tasks', function (Blueprint $table) {
                $table->timestamp('running_review_at')->nullable()->after('running_started_at');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('tasks', 'running_review_at')) {
            Schema::table('tasks', function (Blueprint $table) {
                $table->dropColumn('running_review_at');
            });
        }
    }
};
