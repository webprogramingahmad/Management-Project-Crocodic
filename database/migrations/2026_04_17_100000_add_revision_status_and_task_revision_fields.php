<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('tasks', 'revision_deadline_at')) {
            Schema::table('tasks', function (Blueprint $table) {
                $table->timestamp('revision_deadline_at')->nullable()->after('running_review_at');
                $table->unsignedTinyInteger('revision_hours')->nullable()->after('revision_deadline_at');
            });
        }

        $exists = DB::table('status_tasks')->where('class', 'revision')->exists();
        if (! $exists) {
            DB::table('status_tasks')->insert([
                'id' => (string) Str::uuid(),
                'status' => 'Revision',
                'class' => 'revision',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('status_tasks')->where('class', 'revision')->delete();

        if (Schema::hasColumn('tasks', 'revision_deadline_at')) {
            Schema::table('tasks', function (Blueprint $table) {
                $table->dropColumn(['revision_deadline_at', 'revision_hours']);
            });
        }
    }
};
