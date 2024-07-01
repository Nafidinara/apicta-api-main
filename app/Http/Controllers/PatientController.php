<?php

namespace App\Http\Controllers;

use App\Http\Requests\PatientRequest;
use App\Interfaces\PatientRepositoryInterface;
use App\Interfaces\UserRepositoryInterface;
use DB;
use Illuminate\Support\Facades\Auth;
use Throwable;
use Illuminate\Http\JsonResponse;
use Hash;

class PatientController extends Controller
{
    private PatientRepositoryInterface $patientRepository;
    private UserRepositoryInterface $userRepository;

    public function __construct(
        PatientRepositoryInterface $patientRepository,
        UserRepositoryInterface $userRepository
    )
    {
        $this->patientRepository = $patientRepository;
        $this->userRepository = $userRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $data = $this->patientRepository->getAllPatients();

        return baseResponse(
            'success',
            'success get all patient data',
            $data,
            null,
            200
        );
    }

    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function indexExceptDoctor(string $doctor_id): JsonResponse
    {
        $data = $this->patientRepository->getAllPatientsExceptDoctor($doctor_id);

        return baseResponse(
            'success',
            'success get all patient data',
            $data,
            null,
            200
        );
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param PatientRequest $request
     * @return JsonResponse
     * @throws Throwable
     */
    public function store(PatientRequest $request): JsonResponse
    {

        try {
            $input = $request->all();

            DB::beginTransaction();
            $user = $this->userRepository->createUser([
                'email' => $input['email'],
                'password' => Hash::make($input['password']),
            ]);

            $user->assignRole('patient');

            $input['user_id'] = $user->id;

            $patient = $this->patientRepository->createPatient($input);
            DB::commit();

            return baseResponse(
                'success',
                'success create patient data!',
                $patient,
                null,
                201
            );
        }catch (Throwable $exception){
            DB::rollBack();
            return baseResponse(
                'failed',
                'failed create patient data!',
                null,
                $exception->getMessage(),
                500
            );
        }
    }

    /**
     * Display the specified resource.
     *
     * @param string $patient_id
     * @return JsonResponse
     */
    public function show(string $patient_id): JsonResponse
    {
        try {
            $patient = $this->patientRepository->getPatientById($patient_id);

            $result_patient = collect($patient);

            if ($result_patient['diagnoses'] && Auth::user()->doctor){
                $result_patient['diagnoses'] = collect($patient['diagnoses'])->where('doctor_id',Auth::user()->doctor->id)->values()->all();
            }

            return baseResponse(
                'success',
                'success show patient data!',
                $result_patient,
                null,
                200
            );

        }catch (Throwable $exception){
            return baseResponse(
                'failed',
                'failed show patient data!',
                null,
                $exception->getMessage(),
                500
            );
        }

    }

    /**
     * Update the specified resource in storage.
     *
     * @param PatientRequest $request
     * @param string $patient_id
     * @return JsonResponse
     * @throws Throwable
     */
    public function update(PatientRequest $request, string $patient_id): JsonResponse
    {
        try {
            $data = $request->all();
            DB::beginTransaction();
            $this->patientRepository->updatePatient($patient_id,$data);
            DB::commit();
            return baseResponse(
                'success',
                'success update patient data!',
                null,
                null,
                200
            );
        }catch (Throwable $exception){
            DB::rollBack();
            return baseResponse(
                'failed',
                'failed update patient data!',
                null,
                $exception->getMessage(),
                500
            );
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param string $patient_id
     * @return JsonResponse
     * @throws Throwable
     */
    public function destroy(string $patient_id): JsonResponse
    {

        try {
            DB::beginTransaction();
            $this->patientRepository->deletePatient($patient_id);
            DB::commit();
            return baseResponse(
                'success',
                'success delete patient data!',
                null,
                null,
                200
            );
        }catch (Throwable $exception){
            DB::rollBack();
            return baseResponse(
                'failed',
                'failed delete patient data!',
                null,
                $exception->getMessage(),
                500
            );
        }
    }


}
