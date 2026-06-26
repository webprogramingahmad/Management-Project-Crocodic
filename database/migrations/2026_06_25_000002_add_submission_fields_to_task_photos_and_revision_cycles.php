<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('task_photos', function (Blueprint $table) {
            $table->uuid('submission_id')->nullable()->after('uploaded_by');
            $table->uuid('id_revision_cycle')->nullable()->after('submission_id');

            $table->foreign('submission_id')->references('id')->on('task_submissions')->onDelete('cascade');
            $table->foreign('id_revision_cycle')->references('id')->on('task_revision_cycles')->onDelete('cascade');
        });

        Schema::table('task_revision_cycles', function (Blueprint $table) {
            $table->text('links')->nullable()->after('notes');
        });
    }

    public function down(): void
    {
        Schema::table('task_photos', function (Blueprint $table) {
            $table->dropForeign(['submission_id']);
            $table->dropForeign(['id_revision_cycle']);
            $table->dropColumn(['submission_id', 'id_revision_cycle']);
        });

        Schema::table('task_revision_cycles', function (Blueprint $table) {
            $table->dropColumn('links');
        });
    }
};
