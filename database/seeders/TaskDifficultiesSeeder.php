<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TaskDifficultiesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('task_difficulties')->insert([
            [
                'id' => Str::uuid(),
                'difficulty' => 'Low',
                'class' => 'low'
            ],
            [
                'id' => Str::uuid(),
                'difficulty' => 'Medium',
                'class' => 'medium'
            ],
            [
                'id' => Str::uuid(),
                'difficulty' => 'High',
                'class' => 'high'
            ]
        ]);
    }
}
