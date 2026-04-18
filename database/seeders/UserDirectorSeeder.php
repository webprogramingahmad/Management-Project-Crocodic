<?php

namespace Database\Seeders;

use App\Models\Division;
use App\Models\LastGraduate;
use App\Models\Role;
use App\Models\Statussdm;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserDirectorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $divisi = Division::where('divisi', 'Tester')->first();
        $role = Role::where('role', 'director')->first();
        $status = Statussdm::where('status_sdm', 'Tetap')->first();
        $notReady = Statussdm::where('status_sdm', 'Not Ready')->first();
        $graduate = LastGraduate::where('graduate', 'S3')->first();

        User::factory()->create([
            'id' => (string) Str::uuid(),
            'name' => 'Crocodic2',
            'nik' => '34976921',
            'email' => 'crocodic2@gmail.com',
            'tgl_masuk' => null,
            'tgl_lahir' => null,
            'link_tele' => 'Crocodic2',
            'no_telp' => '022134242437',
            'alamat' => 'Jl. Bima Remaja No.6, Srondol Wetan, Kec. Banyumanik, Kota Semarang, Jawa Tengah 50363',
            'id_graduate' => $graduate->id,
            'id_divisi' => $divisi->id,
            'id_role' => $role->id,
            'id_status_sdm' => $status->id,
            'id_activity_status_sdm' => $notReady->id,
            'password' => Hash::make('crocodic123'),
            'remember_token' => Str::random(10),
        ]);
    }
}
