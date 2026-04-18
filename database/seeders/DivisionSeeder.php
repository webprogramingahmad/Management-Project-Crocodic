<?php

namespace Database\Seeders;

use App\Models\Division;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DivisionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $division = ['Engineer Web', 'Engineer Mobile', 'Analis', 'Content Creator', 'Copywriter', 'UI/UX', 'Tester'];

        foreach ($division as $divisi) {
            Division::create([
                'id' => \Illuminate\Support\Str::uuid(),
                'divisi' => $divisi
            ]);
        }
    }
}
