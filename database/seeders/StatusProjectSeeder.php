<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class StatusProjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('status_projects')->insert([
            [
                'id' => Str::uuid(),
                'status' => 'Maintenance',
                'class' => 'maintenance'
            ],
            [
                'id' => Str::uuid(),
                'status' => 'Running',
                'class' => 'running'
            ],
            [
                'id' => Str::uuid(),
                'status' => 'To do',
                'class' => 'todo'
            ],
            [
                'id' => Str::uuid(),
                'status' => 'Completed',
                'class' => 'completed'
            ]
        ]);
    }
}
