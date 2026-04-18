<?php

namespace Database\Seeders;

use App\Models\LastGraduate;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LastGraduateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $graduates = ['D1', 'D2', 'D3', 'D4', 'SMA/SMK', 'S1', 'S2', 'S3'];

        foreach ($graduates as $graduate) {
            LastGraduate::create([
                'id' => \Illuminate\Support\Str::uuid(),
                'graduate' => $graduate
            ]);
        }
    }
}
