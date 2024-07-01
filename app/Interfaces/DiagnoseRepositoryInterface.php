<?php

namespace App\Interfaces;

interface DiagnoseRepositoryInterface
{
    public function getAllDiagnoses();
    public function getCustomAllDiagnoses($customParameter, $id);
    public function getDiagnoseById($diagnoseId);
    public function deleteDiagnose($diagnoseId);
    public function createDiagnose(array $diagnoseData);
    public function updateDiagnose($diagnoseId, array $diagnoseData);
}
