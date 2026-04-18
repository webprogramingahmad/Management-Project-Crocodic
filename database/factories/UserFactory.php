<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $namadepan = $this->faker->firstName;
        $namabelakang  = $this->faker->lastName;
        $nama  = $namadepan . ' ' . $namabelakang;
        $combinasi = Str::slug($namadepan . '.' . $namabelakang);
        $email = strtolower($combinasi) . $this->faker->unique()->numberBetween(1, 9999) . '@gmail.com';

        $tgl_lahir = $this->faker->dateTimeBetween('-40 years', '-20 years');;
        $tgl_masuk = $this->faker->dateTimeBetween($tgl_lahir, '+20 years');

        $nik = '008' . $tgl_lahir->format('ymd') . $tgl_masuk->format('ymd') . $this->faker->numerify(str_repeat('#', 5));;
        return [
            'id' => (string) Str::uuid(),
            'name' => $nama,
            'nik' => $nik,
            'email' => $email,
            'tgl_lahir' => $tgl_lahir,
            'tgl_masuk' => $tgl_masuk,
            'link_tele' => $this->faker->url,
            'no_telp' => $this->faker->unique()->numerify('08##########'),
            'alamat' => $this->faker->address,
            'id_graduate' => null,
            'id_divisi' => null,
            'id_role' => null,
            'id_status_sdm' => null,
            'password' => Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }
}
