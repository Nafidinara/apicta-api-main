<?php

namespace App\Http\Controllers;

use App\Interfaces\DoctorRepositoryInterface;
use App\Interfaces\PatientRepositoryInterface;
use App\Interfaces\UserRepositoryInterface;
use DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class AdminController extends Controller
{
    private PatientRepositoryInterface $patientRepository;
    private DoctorRepositoryInterface $doctorRepository;

    public function __construct(
        PatientRepositoryInterface $patientRepository,
        DoctorRepositoryInterface $doctorRepository
    )
    {
        $this->patientRepository = $patientRepository;
        $this->doctorRepository = $doctorRepository;
    }

    /**
     * Assign doctor with patients.
     *
     * @param Request $request
     * @return JsonResponse
     * @throws Throwable
     */
    public function assign(Request $request): JsonResponse
    {

        try {
            DB::beginTransaction();
            $input = $request->all();

            $doctor = $this->doctorRepository->getDoctorById($input['doctor_id']);
            $patients = $this->patientRepository->getManyPatientById($input['patient_ids']);

            $validate = array_values($doctor->patients->pluck('id')->toArray());


            foreach ($patients as $patient){
                if (in_array($patient->id, $validate)){
                    continue;
                }else{
                    $patient->doctors()->attach($doctor);
                }
            }

            DB::commit();
            return baseResponse(
                'success',
                'success assign doctor with patients!',
                null,
                null,
                200
            );
        }catch (Throwable $exception){
            DB::rollBack();
            return baseResponse(
                'failed',
                'failed assign doctor with patients!',
                null,
                $exception->getMessage(),
                500
            );
        }
    }

    /**
     * Unassigned doctor with patients.
     *
     * @param Request $request
     * @return JsonResponse
     * @throws Throwable
     */
    public function unassign(Request $request): JsonResponse
    {

        try {
            DB::beginTransaction();
            $input = $request->all();

            $doctor = $this->doctorRepository->getDoctorById($input['doctor_id']);
            $patients = $this->patientRepository->getManyPatientById($input['patient_ids']);


            foreach ($patients as $patient){
                    $patient->doctors()->detach($doctor);
            }

            DB::commit();
            return baseResponse(
                'success',
                'success unassigned doctor with patients!',
                null,
                null,
                200
            );
        }catch (Throwable $exception){
            DB::rollBack();
            return baseResponse(
                'failed',
                'failed unassigned doctor with patients!',
                null,
                $exception->getMessage(),
                500
            );
        }
    }

    /**
     * Unassigned doctor with patients.
     *
     * @param Request $request
     * @return JsonResponse
     * @throws Throwable
     */
    public function unassignByDoctor(Request $request): JsonResponse
    {

        try {
            DB::beginTransaction();
            $input = $request->all();

            $doctors = $this->doctorRepository->getDoctorById($input['doctor_ids']);
            $patient = $this->patientRepository->getManyPatientById($input['patient_id']);


            foreach ($doctors as $doctor){
                $doctor->patients()->detach($patient);
            }

            DB::commit();
            return baseResponse(
                'success',
                'success unassigned doctor with patients!',
                null,
                null,
                200
            );
        }catch (Throwable $exception){
            DB::rollBack();
            return baseResponse(
                'failed',
                'failed unassigned doctor with patients!',
                null,
                $exception->getMessage(),
                500
            );
        }
    }
}
