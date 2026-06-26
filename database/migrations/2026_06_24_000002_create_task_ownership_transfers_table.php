<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('task_ownership_transfers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('id_task');
            $table->uuid('from_user_id');
            $table->uuid('to_user_id');
            $table->uuid('performed_by');
            $table->string('source', 40);
            $table->uuid('request_id')->nullable();
            $table->text('reason')->nullable();
            $table->string('task_status_at_transfer')->nullable();
            $table->timestamps();

            $table->foreign('id_task')->references('id')->on('tasks')->onDelete('cascade');
            $table->foreign('from_user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('to_user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('performed_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('request_id')->references('id')->on('task_ownership_transfer_requests')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_ownership_transfers');
    }
};
