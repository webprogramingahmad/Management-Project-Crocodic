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
        if (Schema::hasTable('statussdms') && Schema::hasColumn('statussdms', 'status_kepegawaian')) {
            Schema::table('statussdms', function (Blueprint $table) {
                $table->dropColumn('status_kepegawaian');
            });
        }

        Schema::dropIfExists('status_kepegawaian');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('statussdms') && !Schema::hasColumn('statussdms', 'status_kepegawaian')) {
            Schema::table('statussdms', function (Blueprint $table) {
                $table->string('status_kepegawaian', 50)->nullable()->after('status_sdm');
            });
        }

        if (!Schema::hasTable('status_kepegawaian')) {
            Schema::create('status_kepegawaian', function (Blueprint $table) {
                $table->char('id', 36)->primary();
                $table->string('status_kepegawaian');
                $table->timestamps();
            });
        }
    }
};

