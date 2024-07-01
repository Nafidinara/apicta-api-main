<?php

namespace App\Repositories;

use App\Interfaces\PatientRepositoryInterface;
use App\Models\Doctor;
use App\Models\DoctorPatient;
use App\Models\Patient;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class PatientRepository implements PatientRepositoryInterface
{
    public function getAllPatients(): \Illuminate\Support\Collection
    {
        return Patient::orderBy('updated_at', 'asc')->get();
    }

    public function getAllPatientsExceptDoctor($doctorId): Collection
    {
        $patients = Patient::all();
        $patient_doctor = Doctor::find($doctorId)->patients;

        return $patients->diff($patient_doctor);
    }

    public function getPatientById($patientId): Model|Collection|Builder|array|null
    {
        return Patient::with(['user', 'doctors', 'diagnoses' => function ($query) {
            $query->orderBy('updated_at', 'desc');
        }])->findOrFail($patientId);

    }

    public function deletePatient($patientId): int
    {
        return Patient::destroy($patientId);
    }

    public function createPatient(array $patientData): Model|Patient
    {
        return Patient::create($patientData);
    }

    public function updatePatient($patientId, array $patientData): bool
    {
        return Patient::find($patientId)->update($patientData);
    }

    public function getManyPatientById($patientIds): Collection
    {
        return Patient::with('user')->findMany($patientIds);
    }
}
