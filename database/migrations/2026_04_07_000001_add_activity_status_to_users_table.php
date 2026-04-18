<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->uuid('id_activity_status_sdm')
                ->nullable()
                ->after('id_status_sdm');

            $table->foreign('id_activity_status_sdm')
                ->references('id')
                ->on('statussdms')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['id_activity_status_sdm']);
            $table->dropColumn('id_activity_status_sdm');
        });
    }
};

