<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\SampleController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ClinicController ;

// System Info
Route::get('/version', function (Request $request) {
    return response()->json([
        'laravel_version' => app()->version(),
        'php_version' => phpversion(),
    ]);
});

// Sample Routes
Route::get('/sample', [SampleController::class, 'index']);
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/validation/validate-tfauth', [AuthController::class, 'validate_tfauth']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [AuthController::class, 'user']);
    Route::post('/auth/profile/security/enable-tfa', [AuthController::class, 'enable_tfauth']);
    Route::post('/auth/profile/security/disable-tfauth', [AuthController::class, 'disable_tfauth']);
    Route::post('/auth/profile/security/update-tfauth', [AuthController::class, 'update_tfauth']);

    // Clinics
    Route::get('/management/users/clinician', [ClinicController::class, 'getAllClinicians']);
    Route::get('/management/users/clinics', [ClinicController::class, 'getAllClinic']);
    Route::post('/management/users/add/clinician', [ClinicController::class, 'addClinician']);
});