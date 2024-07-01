<?php

namespace App\Repositories;

use App\Interfaces\DoctorRepositoryInterface;
use App\Models\Doctor;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class DoctorRepository implements DoctorRepositoryInterface
{
    public function getAllDoctors(): Collection
    {
        return Doctor::all();
    }

    public function getDoctorById($doctorId)
    {
        return Doctor::with('user','patients')->findOrFail($doctorId);
    }

    public function deleteDoctor($doctorId): int
    {
        return Doctor::destroy($doctorId);
    }

    public function createDoctor(array $doctorData): Model|Doctor
    {
        return Doctor::create($doctorData);
    }

    public function updateDoctor($doctorId, array $doctorData): bool
    {
        return Doctor::find($doctorId)->update($doctorData);
    }
}
