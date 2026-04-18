<?php

namespace Database\Seeders;

use App\Models\CategoryAdministration;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategoryAdministrationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = ['Annual', 'Sick', 'Emergency'];

        foreach ($categories as $category) {
            CategoryAdministration::create([
                'id' => Str::uuid(),
                'name' => $category
            ]);
        }
    }
}
