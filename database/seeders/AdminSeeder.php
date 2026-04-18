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
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */

    public function run(): void
    {
        $divisi = Division::where('divisi', 'Tester')->first();
        $role = Role::where('role', 'executive')->first();
        $status = Statussdm::where('status_sdm', 'Tetap')->first();
        $notReady = Statussdm::where('status_sdm', 'Not Ready')->first();
        $graduate = LastGraduate::where('graduate', 'S3')->first();

        User::factory()->create([
            'id' => (string) Str::uuid(),
            'name' => 'Ahmad Nur Azis',
            'nik' => '34976922',
            'email' => 'crocodic1@gmail.com',
            'tgl_masuk' => null,
            'tgl_lahir' => null,
            'link_tele' => 'Crocodic1',
            'no_telp' => '022134242438',
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
