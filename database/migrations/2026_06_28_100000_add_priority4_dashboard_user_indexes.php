<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! $this->indexExists('users', 'users_activity_status_idx')) {
            Schema::table('users', function (Blueprint $table) {
                $table->index('id_activity_status_sdm', 'users_activity_status_idx');
            });
        }

        if (! $this->indexExists('users', 'users_role_idx')) {
            Schema::table('users', function (Blueprint $table) {
                $table->index('id_role', 'users_role_idx');
            });
        }

        if (! $this->indexExists('tasks', 'tasks_user_created_idx')) {
            Schema::table('tasks', function (Blueprint $table) {
                $table->index(['id_user', 'created_at'], 'tasks_user_created_idx');
            });
        }
    }

    public function down(): void
    {
        if ($this->indexExists('tasks', 'tasks_user_created_idx')) {
            Schema::table('tasks', function (Blueprint $table) {
                $table->dropIndex('tasks_user_created_idx');
            });
        }

        if ($this->indexExists('users', 'users_role_idx')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropIndex('users_role_idx');
            });
        }

        if ($this->indexExists('users', 'users_activity_status_idx')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropIndex('users_activity_status_idx');
            });
        }
    }

    private function indexExists(string $table, string $indexName): bool
    {
        if (DB::getDriverName() === 'sqlite') {
            return false;
        }

        $rows = DB::select('SHOW INDEX FROM `'.$table.'` WHERE Key_name = ?', [$indexName]);

        return $rows !== [];
    }
};
