<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ClinicController;
use App\Http\Controllers\Api\ForgotPassword;
use App\Http\Controllers\Api\InvoiceController;
use App\Http\Controllers\Api\IVRRequestController;
use App\Http\Controllers\Api\ManufacturerController;
use App\Http\Controllers\Api\ResetPassword;
use App\Http\Controllers\Api\SampleController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// System Info
Route::get('/version', function (Request $request) {
    return response()->json([
        'laravel_version' => app()->version(),
        'php_version'     => phpversion(),
    ]);
});

//  ############## FORGOT PASSWORD #####################
Route::post('/forgot-password', [ForgotPassword::class, 'sendResetLink']);
Route::post('/reset-password', [ResetPassword::class, 'reset']);

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
    Route::get('/invoices', [InvoiceController::class, 'index']);
    Route::get('/invoices/stats', [InvoiceController::class, 'getStats']);
    Route::get('/invoices/clinics', [InvoiceController::class, 'getClinics']);
    Route::post('/invoices', [InvoiceController::class, 'store']);
    Route::get('/invoices/{invoices}', [InvoiceController::class, 'show']);
    Route::put('/invoices/{invoices}', [InvoiceController::class, 'update']);
    Route::delete('/invoices/{invoices}', [InvoiceController::class, 'destroy']);
    Route::post('/invoices/{invoices}/status', [InvoiceController::class, 'updateStatus']);
    Route::post('/invoices/upload-pdf', [InvoiceController::class, 'uploadPdf']);

    // Clinicians
    Route::get('/management/users/clinician', [ClinicController::class, 'getAllClinicians']);
    Route::post('/management/users/add/clinician', [ClinicController::class, 'addClinician']);
    Route::get('/utility/activity/clinician', [ClinicController::class, 'getAllActivity']);

    // Clinics
    Route::get('/management/users/clinics', [ClinicController::class, 'getAllClinic']);
    Route::post('/management/facilities/clinics', [ClinicController::class, 'addClinic']);

    // Manufacturer
    Route::get('/management/manufacturers', [ManufacturerController::class, 'getAllManufacturers']);
    Route::post('/management/manufacturers', [ManufacturerController::class, 'addManufacturer']);
    Route::get('/management/manufacturers/{id}/archive', [ManufacturerController::class, 'archiveManufacturer']);
    Route::get('/management/manufacturers/{id}/toggle', [ManufacturerController::class, 'toggleManufacturerStatus']);
    Route::delete('/management/manufacturers/{id}', [ManufacturerController::class, 'deleteManufacturer']);
    Route::post('/management/manufacturers/{id}', [ManufacturerController::class, 'updateManufacturer']);
    Route::get('/management/manufacturers/{id}/download', [ManufacturerController::class, 'downloadIVRForm']);

    // Route::get('/manufacturers/{id}/ivr-form', [ManufacturerController::class, 'downloadForm']);
    Route::put('/management/facilities/clinics/{clinicId}/update', [ClinicController::class, 'updateClinic']);
    Route::put('/management/facilities/clinics/{clinicId}/updatestatus', [ClinicController::class, 'updateClinicStatus']);
    Route::put('/management/facilities/clinics/{clinicId}/deleteclinic', [ClinicController::class, 'deleteClinic']);
    Route::put('/management/facilities/clinics/{clinicId}/archiveclinic', [ClinicController::class, 'archiveClinic']);
    Route::put('/management/facilities/clinics/{clinicId}/unarchiveclinic', [ClinicController::class, 'unarchiveClinic']);

    // IVR
    Route::get('/management/ivr/ivrrequests', [IVRRequestController::class, 'getAllIVRRequests']);
    Route::get('/management/ivr/getbrands', [IVRRequestController::class, 'getAllBrands']);
    Route::get('/management/ivr/getclinics', [IVRRequestController::class, 'getAllClinic']);
    Route::get('/management/patients/patientinfo', [IVRRequestController::class, 'getAllPatientInfo']);
    Route::post('/management/add/newivrrequest', [IVRRequestController::class, 'addIVRRequest']);
    Route::put('/management/update/{id}/updateivrrequest', [IVRRequestController::class, 'updateIVRRequest']);
    Route::put('/management/delete/{id}/deleteivrrequest', [IVRRequestController::class, 'deleteIVRRequest']);
    Route::put('/management/archive/{id}/archiveivrrequest', [IVRRequestController::class, 'archiveIVRRequest']);
    Route::put('/management/archive/{id}/unarchiveivrrequest', [IVRRequestController::class, 'unarchiveIVRRequest']);
});