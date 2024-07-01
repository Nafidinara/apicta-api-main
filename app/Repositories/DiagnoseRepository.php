<?php

namespace App\Repositories;

use App\Interfaces\DiagnoseRepositoryInterface;
use App\Models\Diagnose;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class DiagnoseRepository implements DiagnoseRepositoryInterface
{
    public function getAllDiagnoses(): \Illuminate\Support\Collection
    {
        return Diagnose::orderBy('updated_at', 'desc')->get();
    }

    public function getCustomAllDiagnoses($customParameter, $id): Collection
    {
        return Diagnose::with('doctor','patient')->where($customParameter,$id)->orderBy('updated_at', 'desc')->get();
    }

    public function getDiagnoseById($diagnoseId): Model|Diagnose|array|Collection
    {
        return Diagnose::with('doctor','patient')->findOrFail($diagnoseId);
    }

    public function deleteDiagnose($diagnoseId): int
    {
        return Diagnose::destroy($diagnoseId);
    }

    public function createDiagnose(array $diagnoseData): Model|Diagnose
    {
        return Diagnose::create($diagnoseData);
    }

    public function updateDiagnose($diagnoseId, array $diagnoseData)
    {
        return Diagnose::whereId($diagnoseId)->update($diagnoseData);
    }
}
