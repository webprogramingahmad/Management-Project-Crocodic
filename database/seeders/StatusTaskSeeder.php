<?php

namespace Database\Seeders;

use App\Models\StatusTask;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class StatusTaskSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('status_tasks')->insert([
            [
                'id' => Str::uuid(),
                'status' => 'To Do',
                'class' => 'todo'
            ],
            [
                'id' => Str::uuid(),
                'status' => 'In progress',
                'class' => 'progress'
            ],
            [
                'id' => Str::uuid(),
                'status' => 'Review',
                'class' => 'review'
            ],
            [
                'id' => Str::uuid(),
                'status' => 'Revision',
                'class' => 'revision'
            ],
            [
                'id' => Str::uuid(),
                'status' => 'Complete',
                'class' => 'complete'
            ]
        ]);
    }
}
