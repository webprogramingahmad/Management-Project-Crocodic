<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = ['executive', 'staff', 'director'];

        foreach ($roles as $role) {
            Role::create([
                'id' => \Illuminate\Support\Str::uuid(),
                'role' => $role
            ]);
        }
    }
}
