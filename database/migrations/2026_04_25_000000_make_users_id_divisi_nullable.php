<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Executive / director do not use operational division; only staff (engineer) do.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['id_divisi']);
        });

        DB::statement('ALTER TABLE `users` MODIFY `id_divisi` CHAR(36) NULL');

        Schema::table('users', function (Blueprint $table) {
            $table->foreign('id_divisi')
                ->references('id')
                ->on('divisions')
                ->nullOnDelete();
        });
    }

    /**
     * Rollback: fails if any user still has null id_divisi.
     */
    public function down(): void
    {
        if (DB::table('users')->whereNull('id_divisi')->exists()) {
            throw new \RuntimeException('Cannot rollback: set id_divisi for all users with null first.');
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['id_divisi']);
        });

        DB::statement('ALTER TABLE `users` MODIFY `id_divisi` CHAR(36) NOT NULL');

        Schema::table('users', function (Blueprint $table) {
            $table->foreign('id_divisi')
                ->references('id')
                ->on('divisions')
                ->onDelete('cascade');
        });
    }
};
