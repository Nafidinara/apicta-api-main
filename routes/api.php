<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DiagnoseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DoctorController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

//Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//    return $request->user();
//});

// All User Can Access


// Authenticated User Can Access
//Route::middleware(['auth:api'])->group(function () {
//    Route::get('profile', [AuthController::class, 'profile']);
//});
//
//// Only Admin Can Access
//Route::middleware(['auth:api', 'admin'])->group(function () {
//    Route::resource('patients', PatientController::class);
//    Route::resource('doctors', DoctorController::class);
//});

Route::group(['prefix' => 'v1'],function (){

    Route::group(['prefix' => 'account','as' => 'account.'],function (){
        Route::post('/login', [AuthController::class, 'login']);
        Route::get('/profile',[AuthController::class,'profile']);
        Route::get('/logout',[AuthController::class,'logout'])->middleware('auth:api');
    });

//    admin
    Route::group(['prefix' => 'admin','as' => 'admin.', 'middleware' => ['role:admin']],function (){
        Route::post('/patient', [PatientController::class, 'store']);
        Route::get('/patient/doctor/{id}', [PatientController::class, 'indexExceptDoctor']);
        Route::get('/patient', [PatientController::class, 'index']);
        Route::put('/patient/{id}', [PatientController::class, 'update']);
        Route::delete('/patient/{id}', [PatientController::class, 'destroy']);
        Route::get('/patient/{id}', [PatientController::class, 'show']);

        Route::post('/doctor', [DoctorController::class, 'store']);
        Route::get('/doctor', [DoctorController::class, 'index']);
        Route::put('/doctor/{id}', [DoctorController::class, 'update']);
        Route::delete('/doctor/{id}', [DoctorController::class, 'destroy']);
        Route::get('/doctor/{id}', [DoctorController::class, 'show']);

        Route::post('/assign', [AdminController::class, 'assign']);
        Route::post('/unassign', [AdminController::class, 'unassign']);
        Route::post('/unassign/doctor', [AdminController::class, 'unassignByDoctor']);

//        dashboard
        Route::group(['prefix' => 'dashboard','as' => 'dashboard.'],function (){
            Route::get('/total-statistic', [DashboardController::class, 'admin_dashboard_statistic']);
            Route::get('/total-statistic-graph', [DashboardController::class, 'admin_dashboard_graph']);
        });
//        dashboard
    });
//    admin

    //    patient
    Route::group(['prefix' => 'patient','as' => 'patient.', 'middleware' => ['role:doctor|patient']],function (){
        Route::get('/diagnoses', [DiagnoseController::class, 'index']);
        Route::post('/diagnoses', [DiagnoseController::class, 'store']);
        Route::put('/diagnoses/{id}', [DiagnoseController::class, 'update']);
        Route::get('/diagnoses/{id}', [DiagnoseController::class, 'show']);
        Route::post('/predict', [DiagnoseController::class, 'predict_patient']);
    });

    //        dashboard
    Route::group(['prefix' => 'patient/dashboard','as' => 'dashboard.', 'middleware' => ['role:patient']],function (){
        Route::get('/total-statistic', [DashboardController::class, 'patient_dashboard_statistic']);
        Route::get('/latest-diagnose', [DashboardController::class, 'patient_dashboard_latest_diagnose']);
    });
//        dashboard

    //    patient

    //    doctor
    Route::group(['prefix' => 'doctor','as' => 'doctor.', 'middleware' => ['role:doctor']],function (){
        Route::get('/patients', [DoctorController::class, 'getPatientByDoctor']);
        Route::get('/patients/{id}', [PatientController::class, 'show']);
        Route::put('/diagnoses/{id}', [DoctorController::class, 'update']);
        Route::get('/diagnoses/{id}', [DoctorController::class, 'show']);
        Route::post('/predict', [DiagnoseController::class, 'predict_doctor']);
        Route::post('/repredict', [DiagnoseController::class, 'repredict']);

//        dashboard
        Route::group(['prefix' => 'dashboard','as' => 'dashboard.'],function (){
            Route::get('/total-statistic', [DashboardController::class, 'doctor_dashboard_statistic']);
            Route::get('/latest-diagnose', [DashboardController::class, 'doctor_dashboard_latest_diagnose']);
        });
//        dashboard

    });
    //    doctor


//    ML predict
    Route::post('/public/predict', function (Request $request) {
        $file = $request->file('file');
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

            $response = Http::attach('file',file_get_contents($file), $file->getClientOriginalName())
                ->post('https://myoscope.distancing.my.id/predict');

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
    });

});
