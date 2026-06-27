<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        User::query()
            ->whereHas('role', function ($query) {
                $query->whereIn('role', ['executive', 'director']);
            })
            ->whereNotNull('id_divisi')
            ->update(['id_divisi' => null]);
    }

    public function down(): void
    {
        // Division assignment for executive/director is not restored.
    }
};
