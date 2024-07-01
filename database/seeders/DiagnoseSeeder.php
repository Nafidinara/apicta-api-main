<?php

namespace Database\Seeders;

use App\Models\Diagnose;
use App\Models\Doctor;
use App\Models\Patient;
use Illuminate\Database\Seeder;
class DiagnoseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $conditions = collect(['normal','mi']);
        $notes = [
            'normal' => "kondisi normal, namun perlu istirahat",
            'mi' => "perlu tindakan secepatnya dan pemantauan"
        ];

        for ($x = 0; $x < 50; $x++){
            $doctor = Doctor::inRandomOrder()->first();
            $patient = Patient::inRandomOrder()->first();
            $c = $conditions->shuffle()->first();
            Diagnose::create([
                'patient_id' =>$patient->id,
                'doctor_id' =>$doctor->id,
                'diagnoses' => $c,
                'notes' => $notes[$c],
                'is_verified' => false,
                'file' => 'apicta/963q79jNwGT8Pqhs910e1eC57hZbWugnXnbYb2wr.wav'
            ]);
        }
    }
}
