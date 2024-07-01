<?php

namespace App\Http\Controllers;

use App\Http\Requests\DiagnoseRequest;
use App\Interfaces\DiagnoseRepositoryInterface;
use App\Interfaces\PatientRepositoryInterface;
use App\Interfaces\UserRepositoryInterface;
use Auth;
use DB;
use Http;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Storage;
use Throwable;

class DiagnoseController extends Controller
{

    private DiagnoseRepositoryInterface $diagnoseRepository;
    private  PatientRepositoryInterface $patientRepository;

    public function __construct(
        DiagnoseRepositoryInterface $diagnoseRepository,
        UserRepositoryInterface $userRepository,
        PatientRepositoryInterface $patientRepository
    )
    {
        $this->diagnoseRepository = $diagnoseRepository;
        $this->patientRepository = $patientRepository;

        $this->middleware('role:doctor', [
            'only' => ['update'],
            'except' => ['store']
        ]);
        $this->middleware('role:patient', [
            'only' => ['store'],
            'except' => ['update']
        ]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $user = Auth::user();

        $data = $this->diagnoseRepository->getCustomAllDiagnoses($user->roles[0]['name'].'_id',$user->role_data['id']);

        return baseResponse(
            'success',
            'success get all diagnose data',
            $data,
            null,
            200
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param DiagnoseRequest $request
     * @return JsonResponse
     * @throws Throwable
     */
    public function store(DiagnoseRequest $request): JsonResponse
    {

        try {
            $input = $request->all();
            $user = Auth::user();
            $patient = $user->patient;
            $doctor = $patient->doctors[0];

            if (!$patient || !$doctor){
                return baseResponse(
                    'failed',
                    'please make sure you already assign with doctor, or asking admin!',
                    null,
                    null,
                    500
                );
            }

            $input['patient_id'] = $patient->id;
            $input['doctor_id'] = $doctor->id;

            $input['file'] = Storage::disk('s3')->put('apicta', $input['file']);

            $diagnose = $this->diagnoseRepository->createDiagnose($input);
            DB::commit();

            return baseResponse(
                'success',
                'success create diagnose data!',
                $diagnose,
                null,
                201
            );
        }catch (Throwable $exception){
            DB::rollBack();
            return baseResponse(
                'failed',
                'failed create diagnose data!',
                null,
                $exception->getMessage(),
                500
            );
        }
    }

    /**
     * Display the specified resource.
     *
     * @param string $diagnose_id
     * @return JsonResponse
     */
    public function show(string $diagnose_id): JsonResponse
    {
        try {
            $diagnose = $this->diagnoseRepository->getDiagnoseById($diagnose_id);

            return baseResponse(
                'success',
                'success show diagnose data!',
                $diagnose,
                null,
                200
            );
        }catch (Throwable $exception){
            return baseResponse(
                'failed',
                'failed show diagnose data!',
                null,
                $exception->getMessage(),
                500
            );
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param DiagnoseRequest $request
     * @param string $diagnose_id
     * @return JsonResponse
     * @throws Throwable
     */
    public function update(DiagnoseRequest $request, string $diagnose_id): JsonResponse
    {
        try {
            $data = $request->all();
            DB::beginTransaction();

            $diagnose_data = $this->diagnoseRepository->getDiagnoseById($diagnose_id);

            $this->patientRepository->updatePatient($diagnose_data->patient_id, ['condition' => $data['diagnoses']]);

            $this->diagnoseRepository->updateDiagnose($diagnose_id,$data);

            DB::commit();
            return baseResponse(
                'success',
                'success update diagnose data!',
                null,
                null,
                200
            );
        }catch (Throwable $exception){
            DB::rollBack();
            return baseResponse(
                'failed',
                'failed update diagnose data!',
                null,
                $exception->getMessage(),
                500
            );
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param string $diagnose_id
     * @return JsonResponse
     * @throws Throwable
     */
    public function destroy(string $diagnose_id): JsonResponse
    {

        try {
            DB::beginTransaction();
            $this->diagnoseRepository->deleteDiagnose($diagnose_id);
            DB::commit();
            return baseResponse(
                'success',
                'success delete diagnose data!',
                null,
                null,
                200
            );
        }catch (Throwable $exception){
            DB::rollBack();
            return baseResponse(
                'failed',
                'failed delete diagnose data!',
                null,
                $exception->getMessage(),
                500
            );
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Request $request
     * @return JsonResponse
     * @throws Throwable
     */
    public function predict_patient(Request $request)
    {

        $file = $request->file('file');
        $patient_id = $request->input('patient_id');

        if (!$file){
            return baseResponse(
                'failed',
                'please make sure you sending the file',
                null,
                null,
                500
            );
        }

        if (!$patient_id){
            return baseResponse(
                'failed',
                'please make sure you sending the patient id',
                null,
                null,
                500
            );
        }

        try {
            DB::beginTransaction();
            $response = Http::attach('file',file_get_contents($file), $file->getClientOriginalName())
                ->post('https://myoscope.distancing.my.id/predict');

            $patient = $this->patientRepository->getPatientById($patient_id);

            $file_path = Storage::disk('s3')->put('apicta', $file);

            $this->diagnoseRepository->createDiagnose([
                'patient_id' =>$patient->id,
                'doctor_id' =>$patient->doctors[0]->id,
                'diagnoses' => $response['result'] ? 'mi' : 'normal',
                'notes' => '-',
                'is_verified' => false,
                'file' => $file_path
            ]);

            DB::commit();
            return baseResponse(
                'success',
                'success predict',
                $response->json(),
                null,
                200
            );

        }catch (Throwable $exception) {
            DB::rollBack();
            return baseResponse(
                'failed',
                'error when predict',
                null,
                $exception->getMessage(),
                500
            );
        }
    }

    public function predict_doctor(Request $request)
    {

        $file = $request->file('file');
        $patient_id = $request->input('patient_id');

        if (!$file){
            return baseResponse(
                'failed',
                'please make sure you sending the file',
                null,
                null,
                500
            );
        }

        if (!$patient_id){
            return baseResponse(
                'failed',
                'please make sure you sending the patient id',
                null,
                null,
                500
            );
        }

        try {
            DB::beginTransaction();
            $response = Http::attach('file',file_get_contents($file), $file->getClientOriginalName())
                ->post('https://myoscope.distancing.my.id/predict');

            $patient = $this->patientRepository->getPatientById($patient_id);

            $file_path = Storage::disk('s3')->put('apicta', $file);

            $this->diagnoseRepository->createDiagnose([
                'patient_id' =>$patient->id,
                'doctor_id' =>Auth::user()->doctor->id,
                'diagnoses' => $response['result'] ? 'mi' : 'normal',
                'notes' => '-',
                'is_verified' => false,
                'file' => $file_path
            ]);

            DB::commit();

            return baseResponse(
                'success',
                'success predict',
                $response->json(),
                null,
                200
            );

        }catch (Throwable $exception) {
            DB::rollBack();
            return baseResponse(
                'failed',
                'error when predict',
                null,
                $exception->getMessage(),
                500
            );
        }
    }

    public function repredict(Request $request){
        $file_path = $request->input('file_path');

        if (!$file_path){
            return baseResponse(
                'failed',
                'please make sure you sending the file',
                null,
                null,
                500
            );
        }

        $file = Storage::disk('s3')->get($file_path);

        $temp_file = tempnam(sys_get_temp_dir(), 's3');
        file_put_contents($temp_file, $file);


        if (!$file){
            return baseResponse(
                'failed',
                'please make sure you sending the file',
                null,
                null,
                500
            );
        }

        try {

//            $file_path = 'apicta/963q79jNwGT8Pqhs910e1eC57hZbWugnXnbYb2wr.wav';
//            $file = Storage::disk('s3')->get($file_path);
//
//            $local_path = 'files-alfara/alfara.wav';
//            $res = Storage::disk('public')->put($local_path, $file);

//            dd(file_get_contents(storage_path('app/public/files-alfara/alfara.wav')));

            $response = Http::attach('file', file_get_contents($temp_file), basename($file_path))
                ->post('https://myoscope.distancing.my.id/predict');
//
//            $response = Http::attach('file', file_get_contents($temp_file), 'ddddddd.wav');

//            dd($response);

            return baseResponse(
                'success',
                'success predict',
                $response->json(),
                null,
                200
            );

        }catch (Throwable $exception){
            return baseResponse(
                'failed',
                'error when predict',
                null,
                $exception->getMessage(),
                500
            );
        }
    }
}
