<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Map canonical role keys stored in `roles.role` (internal identifiers).
     */
    public function up(): void
    {
        DB::table('roles')->where('role', 'admin')->update(['role' => 'executive']);
        DB::table('roles')->where('role', 'user')->update(['role' => 'staff']);
        DB::table('roles')->where('role', 'project director')->update(['role' => 'director']);
    }

    public function down(): void
    {
        DB::table('roles')->where('role', 'director')->update(['role' => 'project director']);
        DB::table('roles')->where('role', 'staff')->update(['role' => 'user']);
        DB::table('roles')->where('role', 'executive')->update(['role' => 'admin']);
    }
};
