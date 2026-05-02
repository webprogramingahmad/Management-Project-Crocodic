<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('status_tasks', function (Blueprint $table) {
            $table->index('class', 'status_tasks_class_idx');
            $table->index('status', 'status_tasks_status_idx');
        });

        Schema::table('administrations', function (Blueprint $table) {
            $table->index(['id_user', 'start_date', 'end_date', 'id_status'], 'adm_user_date_status_idx');
            $table->index(['start_date', 'end_date', 'id_status'], 'adm_date_status_idx');
        });

        Schema::table('tasks', function (Blueprint $table) {
            $table->index(['id_status', 'updated_at'], 'tasks_status_updated_idx');
            $table->index(['id_user', 'id_status'], 'tasks_user_status_idx');
            $table->index(['id_project', 'id_status'], 'tasks_project_status_idx');
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropIndex('tasks_status_updated_idx');
            $table->dropIndex('tasks_user_status_idx');
            $table->dropIndex('tasks_project_status_idx');
        });

        Schema::table('administrations', function (Blueprint $table) {
            $table->dropIndex('adm_user_date_status_idx');
            $table->dropIndex('adm_date_status_idx');
        });

        Schema::table('status_tasks', function (Blueprint $table) {
            $table->dropIndex('status_tasks_class_idx');
            $table->dropIndex('status_tasks_status_idx');
        });
    }
};
