<?php

namespace Database\Seeders;

use App\Models\Statussdm;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StatussdmSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $status_sdm = ['Tetap', 'Kontrak', 'Magang', 'Ready', 'Stand By', 'Not Ready', 'Absent'];

        foreach ($status_sdm as $status) {
            Statussdm::create([
                'id' => \Illuminate\Support\Str::uuid(),
                'status_sdm' => $status
            ]);
        }
    }
}
