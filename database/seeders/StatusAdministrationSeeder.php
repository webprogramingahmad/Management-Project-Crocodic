<?php

namespace Database\Seeders;

use App\Models\StatusAdministration;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class StatusAdministrationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $status = ['pending', 'reject', 'accept'];

        foreach ($status as $s) {
            StatusAdministration::create([
                'id' => Str::uuid(),
                'name' => $s
            ]);
        }
    }
}
