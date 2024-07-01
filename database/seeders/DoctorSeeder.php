<?php

namespace Database\Seeders;

use App\Models\Doctor;
use App\Models\User;
use Hash;
use Illuminate\Database\Seeder;

class DoctorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $uuids = [
            '02b68875-5092-387d-8cef-f29a629bbe5e',
            '18f87aa5-d695-3cdf-92f9-a73328c4b566',
            '8dba1f8e-75de-37a1-84be-f55dbc55bb0c',
        ];

        for ($x = 1; $x <= 3; $x++) {
            $user = User::create([
                'email' => 'doctor' . $x . '@mail.com',
                'password' => Hash::make('password')
            ]);

            $user->assignRole('doctor');

            $genders = collect(['male','female']);

            Doctor::create([
                'id' => $uuids[$x - 1],
                'user_id' => $user->id,
                'fullname' => fake()->name(),
                'address' => fake()->address(),
                'phone' => fake()->phoneNumber(),
                'emergency_phone' => fake()->phoneNumber(),
                'gender' => $genders->shuffle()->first(),
                'age' => 20,
            ]);
        }
    }
}
