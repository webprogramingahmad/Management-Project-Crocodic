<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('task_revision_cycles', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->char('id_task', 36);
            $table->unsignedInteger('cycle_number');
            $table->timestamp('entered_revision_at');
            $table->timestamp('exited_revision_at')->nullable();
            $table->timestamp('deadline_at')->nullable();
            $table->unsignedTinyInteger('revision_hours')->nullable();
            $table->timestamps();

            $table->foreign('id_task')->references('id')->on('tasks')->onDelete('cascade');
            $table->unique(['id_task', 'cycle_number'], 'task_revision_cycles_task_cycle_unique');
            $table->index(['id_task', 'entered_revision_at'], 'task_revision_cycles_task_entered_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_revision_cycles');
    }
};

