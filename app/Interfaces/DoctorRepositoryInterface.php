<?php

namespace App\Interfaces;

interface DoctorRepositoryInterface
{
    public function getAllDoctors();
    public function getDoctorById($doctorId);
    public function deleteDoctor($doctorId);
    public function createDoctor(array $doctorData);
    public function updateDoctor($doctorId, array $doctorData);
}
