<?php

namespace App\Http\Controllers;

use App\Interfaces\DiagnoseRepositoryInterface;
use App\Interfaces\DoctorRepositoryInterface;
use App\Interfaces\PatientRepositoryInterface;
use Auth;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Traits\EnumeratesValues;

class DashboardController extends Controller
{
    private DoctorRepositoryInterface $doctorRepository;
    private PatientRepositoryInterface $patientRepository;

    private DiagnoseRepositoryInterface $diagnoseRepository;

    public function __construct(
        DoctorRepositoryInterface $doctorRepository,
        PatientRepositoryInterface $patientRepository,
        DiagnoseRepositoryInterface $diagnoseRepository
    )
    {
        $this->doctorRepository = $doctorRepository;
        $this->patientRepository = $patientRepository;
        $this->diagnoseRepository = $diagnoseRepository;
    }

    public function admin_dashboard_statistic(): JsonResponse
    {
//        jadi nanti ada beberapa card yang isinya:
//        1. total dokter
//        2. total pasien
//        3. total normal
//        4. total mi
//        5. total gender
//        6. total patient today

        $total_doctor = count($this->doctorRepository->getAllDoctors());
        $total_patient = count($this->patientRepository->getAllPatients());
        $total_patient_normal = collect($this->patientRepository->getAllPatients())->where('condition','normal')->count();
        $total_patient_mi = collect($this->patientRepository->getAllPatients())->where('condition','mi')->count();
        $total_patient_male = collect($this->patientRepository->getAllPatients())->where('gender','male')->count();
        $total_patient_female = collect($this->patientRepository->getAllPatients())->where('gender','female')->count();
        $total_patient_today = collect($this->patientRepository->getAllPatients())
            ->filter(function ($patient) {
                return Carbon::parse($patient->updated_at)->isToday();
            })
            ->count();

        return baseResponse(
            'success',
            'success admin_get_total_statistic!',
            [
                'total_doctor' => $total_doctor,
                'total_patient' => $total_patient,
                'total_patient_normal' => $total_patient_normal,
                'total_patient_mi' => $total_patient_mi,
                'total_patient_male' => $total_patient_male,
                'total_patient_female' => $total_patient_female,
                'total_patient_today' => $total_patient_today
            ],
            null,
            200
        );
    }

    private function get_patient_by_date($start, $end): EnumeratesValues|Collection
    {
        return collect($this->patientRepository->getAllPatients())
            ->whereBetween('updated_at', [$start, $end]);
    }
    public function admin_dashboard_graph(Request $request): JsonResponse
    {
//        2. patient weekly (done)
//        3. patient monthly
//        4. custom date
//        5. tinggal bikin kondisi lagi

        $filter = $request->query('filter');

        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        $organizedData = [];

        $patients = $this->patientRepository->getAllPatients();

        if ($filter === 'weekly'){

            for ($date = $startOfWeek; $date->lte($endOfWeek); $date->addDay()) {
                // Your code here
                $_date = Carbon::parse($date);

                $organizedData[$_date->format('Y-m-d')] = collect($patients)->where('updated_at', '>=', $_date->startOfDay())->where('updated_at', '<=', $_date->endOfDay())->count();
            }

        }elseif ($filter === 'monthly'){

            for ($date = $startOfMonth; $date->lte($endOfMonth); $date->addWeek()) {
                // Your code here
                $_date = Carbon::parse($date);

                $organizedData['week ' . $_date->weekOfMonth] = collect($patients)->where('updated_at', '>=', $_date->startOfWeek())->where('updated_at', '<=', $_date->endOfWeek())->count();
            }

        }
        elseif ($filter === 'custom'){
            if (!$request->query('end_date') || !$request->query('start_date')){
                return baseResponse(
                    'error',
                    'error admin_dashboard_graph!',
                    null,
                    null,
                    500
                );
            }

            $customStart = Carbon::createFromFormat('Y-m-d', $request->query('start_date'));
            $customEnd = Carbon::createFromFormat('Y-m-d', $request->query('end_date'));

            for ($date = $customStart; $date->lte($customEnd); $date->addDay()) {
                // Your code here
                $_date = Carbon::parse($date);

                $organizedData[$_date->format('Y-m-d')] = collect($patients)->where('updated_at', '>=', $_date->startOfDay())->where('updated_at', '<=', $_date->endOfDay())->count();
            }

        }
        else{
            for ($date = $startOfWeek; $date->lte($endOfWeek); $date->addDay()) {
                // Your code here
                $_date = Carbon::parse($date);

                $organizedData[$_date->format('Y-m-d')] = collect($patients)->where('updated_at', '>=', $_date->startOfDay())->where('updated_at', '<=', $_date->endOfDay())->count();
            }
        }

        return baseResponse(
            'success',
            'success admin_get_total_statistic!',
            [
                'patient' => $organizedData
            ],
            null,
            200
        );

    }

    public function doctor_dashboard_statistic(): JsonResponse
    {
//        jadi nanti ada beberapa card yang isinya:
//        1. total pasien
//        2. total verified
//        3. total unverified
//        4. total mi
//        5. total normal
//        6. total gender

        $user = Auth::user();
        $patients = $user->doctor->patients;
        $diagnoses = $this->diagnoseRepository->getCustomAllDiagnoses('doctor_id', $user->doctor->id);

        $total_patient = collect($patients)->count();
        $total_verified = collect($diagnoses)->where('is_verified',1)->count();
        $total_unverified = collect($diagnoses)->where('is_verified',0)->count();
        $total_patient_normal = collect($patients)->where('condition','normal')->count();
        $total_patient_mi = collect($patients)->where('condition','mi')->count();
        $total_patient_male = collect($patients)->where('gender','male')->count();
        $total_patient_female = collect($patients)->where('gender','female')->count();

        return baseResponse(
            'success',
            'success doctor_get_total_statistic!',
            [
                'total_patient' => $total_patient,
                'total_verified' => $total_verified,
                'total_unverified' => $total_unverified,
                'total_patient_normal' => $total_patient_normal,
                'total_patient_mi' => $total_patient_mi,
                'total_patient_male' => $total_patient_male,
                'total_patient_female' => $total_patient_female,
            ],
            null,
            200
        );
    }

    public function doctor_dashboard_latest_diagnose(): JsonResponse
    {
//        get latest diagnose
        $user = Auth::user();
        $diagnoses = collect($this->diagnoseRepository->getCustomAllDiagnoses('doctor_id', $user->doctor->id))->sortByDesc('updated_at')->slice(0,5)->values()->toArray();

        return baseResponse(
            'success',
            'success doctor_dashboard_latest_diagnose!',
            [
                'diagnoses' => $diagnoses
            ],
            null,
            200
        );

    }

    public function patient_dashboard_statistic(): JsonResponse
    {
//        jadi nanti ada beberapa card yang isinya:
//        1. total verified
//        2. total unverified
//        3. total mi
//        4. total normal

        $user = Auth::user();

        $diagnoses = $user->patient->diagnoses;

        $total_verified = collect($diagnoses)->where('is_verified',1)->count();
        $total_unverified = collect($diagnoses)->where('is_verified',0)->count();
        $total_patient_normal = collect($diagnoses)->where('diagnoses','normal')->count();
        $total_patient_mi = collect($diagnoses)->where('diagnoses','mi')->count();

        return baseResponse(
            'success',
            'success patient_get_total_statistic!',
            [
                'total_verified' => $total_verified,
                'total_unverified' => $total_unverified,
                'total_patient_normal' => $total_patient_normal,
                'total_patient_mi' => $total_patient_mi,
            ],
            null,
            200
        );
    }

    public function patient_dashboard_latest_diagnose(): JsonResponse
    {
//        get latest diagnose
        $user = Auth::user();
        $diagnoses = collect($user->patient->diagnoses)->sortByDesc('updated_at')->slice(0,5)->values()->toArray();

        return baseResponse(
            'success',
            'success patient_dashboard_latest_diagnose!',
            [
                'diagnoses' => $diagnoses
            ],
            null,
            200
        );

    }
}
