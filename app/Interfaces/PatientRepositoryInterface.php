<?php

namespace App\Interfaces;

interface PatientRepositoryInterface
{
    public function getAllPatients();
    public function getPatientById($patientId);
    public function getManyPatientById($patientIds);
    public function deletePatient($patientId);
    public function createPatient(array $patientData);
    public function updatePatient($patientId, array $patientData);

    public function getAllPatientsExceptDoctor($doctorId);
}
