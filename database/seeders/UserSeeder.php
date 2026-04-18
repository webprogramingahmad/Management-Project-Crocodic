<?php

namespace Database\Seeders;

use App\Models\Division;
use App\Models\LastGraduate;
use App\Models\PendidikanTerakhir;
use App\Models\Role;
use App\Models\Statussdm;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $divisiIDs = Division::pluck('id')->toArray();
        $role = Role::where('role', 'staff')->first();
        $employmentIds = Statussdm::employmentTypeIds();
        $activityIds = Statussdm::activityStatusIds();
        $graduateIDs = LastGraduate::pluck('id')->toArray();

        User::factory(100)->make()->each(function ($user) use ($divisiIDs, $employmentIds, $activityIds, $role, $graduateIDs) {
            $user->id_divisi = fake()->randomElement($divisiIDs);
            $user->id_role = $role?->id;
            $user->id_status_sdm = $employmentIds !== [] && fake()->boolean(60)
                ? fake()->randomElement($employmentIds)
                : null;
            $user->id_activity_status_sdm = $activityIds !== []
                ? fake()->randomElement($activityIds)
                : null;
            $user->id_graduate = fake()->randomElement($graduateIDs);
            $user->save();
        });
    }
}
