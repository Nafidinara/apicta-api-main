<?php

namespace App\Http\Controllers;

use App\Http\Requests\DoctorRequest;
use App\Interfaces\DoctorRepositoryInterface;
use App\Interfaces\UserRepositoryInterface;
use Auth;
use DB;
use Throwable;
use Illuminate\Http\JsonResponse;
use Hash;

class DoctorController extends Controller
{

    private DoctorRepositoryInterface $doctorRepository;
    private UserRepositoryInterface $userRepository;

    public function __construct(
        DoctorRepositoryInterface $doctorRepository,
        UserRepositoryInterface $userRepository
    )
    {
        $this->doctorRepository = $doctorRepository;
        $this->userRepository = $userRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $data = $this->doctorRepository->getAllDoctors();

        return baseResponse(
            'success',
            'success get all doctor data',
            $data,
            null,
            200
        );
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param DoctorRequest $request
     * @return JsonResponse
     * @throws Throwable
     */
    public function store(DoctorRequest $request): JsonResponse
    {

        try {
            $input = $request->all();

            DB::beginTransaction();
            $user = $this->userRepository->createUser([
                'email' => $input['email'],
                'password' => Hash::make($input['password']),
            ]);

            $user->assignRole('doctor');

            $input['user_id'] = $user->id;

            $doctor = $this->doctorRepository->createDoctor($input);
            DB::commit();

            return baseResponse(
                'success',
                'success create doctor data!',
                $doctor,
                null,
                201
            );
        }catch (Throwable $exception){
            DB::rollBack();
            return baseResponse(
                'failed',
                'failed create doctor data!',
                null,
                $exception->getMessage(),
                500
            );
        }
    }

    /**
     * Display the specified resource.
     *
     * @param string $doctor_id
     * @return JsonResponse
     */
    public function show(string $doctor_id): JsonResponse
    {
        try {
            $doctor = $this->doctorRepository->getDoctorById($doctor_id);

            return baseResponse(
                'success',
                'success show doctor data!',
                $doctor,
                null,
                200
            );
        }catch (Throwable $exception){
            return baseResponse(
                'failed',
                'failed show doctor data!',
                null,
                $exception->getMessage(),
                500
            );
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param DoctorRequest $request
     * @param string $doctor_id
     * @return JsonResponse
     * @throws Throwable
     */
    public function update(DoctorRequest $request, string $doctor_id): JsonResponse
    {
        try {
            $data = $request->all();
            DB::beginTransaction();
            $this->doctorRepository->updateDoctor($doctor_id,$data);
            DB::commit();
            return baseResponse(
                'success',
                'success update doctor data!',
                null,
                null,
                200
            );
        }catch (Throwable $exception){
            DB::rollBack();
            return baseResponse(
                'failed',
                'failed update doctor data!',
                null,
                $exception->getMessage(),
                500
            );
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param string $doctor_id
     * @return JsonResponse
     * @throws Throwable
     */
    public function destroy(string $doctor_id): JsonResponse
    {

        try {
            DB::beginTransaction();
            $this->doctorRepository->deleteDoctor($doctor_id);
            DB::commit();
            return baseResponse(
                'success',
                'success delete doctor data!',
                null,
                null,
                200
            );
        }catch (Throwable $exception){
            DB::rollBack();
            return baseResponse(
                'failed',
                'failed delete doctor data!',
                null,
                $exception->getMessage(),
                500
            );
        }
    }

        /**
         * Get patient list by doctor authentication.
         *
         * @return JsonResponse
         * @throws Throwable
         */
    public function getPatientByDoctor(): JsonResponse
    {

        try {
            $user = Auth::user();
            $doctor = $user->doctor;
            if (!$doctor){
                return baseResponse(
                    'failed',
                    'failed get list patient data!',
                    null,
                    null,
                    500
                );
            }
            return baseResponse(
                'success',
                'success get list patient data!',
                $doctor->patients,
                null,
                200
            );
        }catch (Throwable $exception){
            return baseResponse(
                'failed',
                'failed get list patient data!',
                null,
                $exception->getMessage(),
                500
            );
        }
    }
}
