<?php

namespace Database\Seeders;

use App\Models\Doctor;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Database\Seeder;
use Hash;

class PatientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        for ($x = 1; $x <= 25; $x++){
            $user = User::create([
                'email' => 'patient'.$x.'@mail.com',
                'password' => Hash::make('password')
            ]);

            $user->assignRole('patient');

            $genders = collect(['male','female']);
            $conditions = collect(['normal','mi']);

            $patient = Patient::create([
                'id' => fake()->uuid(),
                'user_id' => $user->id,
                'fullname' => fake()->name(),
                'address' => fake()->address(),
                'phone' => fake()->phoneNumber(),
                'emergency_phone' => fake()->phoneNumber(),
                'gender' => $genders->shuffle()->first(),
                'age' => rand(9,70),
                'device_id' => fake()->uuid(),
                'condition' => $conditions->shuffle()->first(),
            ]);
        }

        $patients = Patient::all();

        foreach ($patients as $p){
            $doctor = Doctor::inRandomOrder()->first();
            $p->doctors()->attach($doctor);
        }

    }
}
