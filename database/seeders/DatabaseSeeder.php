<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        $this->call([
            DivisionSeeder::class,
            StatussdmSeeder::class,
            RoleSeeder::class,
            LastGraduateSeeder::class,
            UserSeeder::class,
            AdminSeeder::class,
            DirectorSeeder::class,
            UserDirectorSeeder::class,
            UserUserSeeder::class,
            ProjectDifficultySeeder::class,
            StatusProjectSeeder::class,
            StatusTaskSeeder::class,
            TaskDifficultiesSeeder::class,
            CategoryAdministrationSeeder::class,
            StatusAdministrationSeeder::class
        ]);
    }
}
