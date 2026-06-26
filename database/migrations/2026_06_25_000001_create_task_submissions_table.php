<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('task_submissions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('id_task');
            $table->string('type', 20);
            $table->unsignedTinyInteger('cycle_number')->default(1);
            $table->text('notes');
            $table->text('links')->nullable();
            $table->uuid('submitted_by');
            $table->timestamps();

            $table->foreign('id_task')->references('id')->on('tasks')->onDelete('cascade');
            $table->foreign('submitted_by')->references('id')->on('users')->onDelete('cascade');
            $table->unique(['id_task', 'type', 'cycle_number'], 'task_submissions_task_type_cycle_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_submissions');
    }
};
