<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('task_ownership_transfer_requests', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('id_task');
            $table->uuid('requested_by');
            $table->uuid('from_user_id');
            $table->uuid('to_user_id');
            $table->text('reason');
            $table->string('status', 20)->default('pending');
            $table->uuid('reviewed_by')->nullable();
            $table->text('review_note')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->foreign('id_task')->references('id')->on('tasks')->onDelete('cascade');
            $table->foreign('requested_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('from_user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('to_user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('reviewed_by')->references('id')->on('users')->onDelete('set null');

            $table->index(['id_task', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_ownership_transfer_requests');
    }
};
