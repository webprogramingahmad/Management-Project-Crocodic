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
        Schema::create('projects', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->date('start_date');
            $table->date('end_date');
            $table->uuid('id_difficulty')->nullable();
            $table->uuid('id_status');
            $table->longText('description')->nullable();
            $table->uuid('id_director');
            $table->timestamps();

            $table->foreign('id_difficulty')->references('id')->on('project_difficulties')->onDelete('cascade');
            $table->foreign('id_status')->references('id')->on('status_projects')->onDelete('cascade');
            $table->foreign('id_director')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::create('project_user', function (Blueprint $table) {
            $table->uuid('project_id');
            $table->uuid('user_id');

            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->primary(['project_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_user');
        Schema::dropIfExists('projects');
    }
};
