<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ClinicController;
use App\Http\Controllers\Api\ForgotPassword;
use App\Http\Controllers\Api\InvoiceController;
use App\Http\Controllers\Api\IVRRequestController;
use App\Http\Controllers\Api\ManufacturerController;
use App\Http\Controllers\Api\BrandController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\GraftSizeController;
use App\Http\Controllers\Api\ResetPassword;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\SampleController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\InventoryController;


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
Route::post('/magic-order-auth', [OrderController::class, 'validateMagicLink']);
Route::post('/magic-ivr-auth', [IVRRequestController::class, 'validateMagicLinkIVR']);

// Public API Call - for magic links
Route::get('/management/public/order/getgraftsizes', [OrderController::class, 'getAllGraftSizes']);
Route::put('/management/public/magicorder/update/{id}/updateorderstatus', [OrderController::class, 'updateMagicOrderStatus']);

// Sample Routes
Route::get('/sample', [SampleController::class, 'index']);
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/validation/validate-tfauth', [AuthController::class, 'validate_tfauth']);

// ivr file stream
Route::get('/private-file/{path}', [IVRRequestController::class, 'viewIVRFile'])
->where('path', '.*');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [AuthController::class, 'user']);
    Route::post('/auth/profile/security/enable-tfa', [AuthController::class, 'enable_tfauth']);
    Route::post('/auth/profile/security/disable-tfauth', [AuthController::class, 'disable_tfauth']);
    Route::post('/auth/profile/security/update-tfauth', [AuthController::class, 'update_tfauth']);
    Route::get('/invoice-management', [InvoiceController::class, 'index']);
    Route::get('/invoice-management/stats', [InvoiceController::class, 'getStats']);
    Route::get('/invoice-management/clinics', [InvoiceController::class, 'getClinics']);
    Route::post('/invoice-management', [InvoiceController::class, 'store']);
    Route::get('/invoice-management/{invoice}', [InvoiceController::class, 'show']);
    Route::put('/invoice-management/{invoice}', [InvoiceController::class, 'update']);
    Route::delete('/invoice-management/{invoice}', [InvoiceController::class, 'destroy']);
    Route::post('/invoice-management/{invoice}/status', [InvoiceController::class, 'updateStatus']);
    Route::post('/invoice-management/upload-pdf', [InvoiceController::class, 'uploadPdf']);
    Route::post('/invoice-management/sync-google-sheet', [InvoiceController::class, 'syncWithGoogleSheet']);

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

    // User management routes
    Route::get('/users/stats', [UserController::class, 'stats']);
    Route::get('/users', [UserController::class, 'index']);
    Route::post('/users', [UserController::class, 'store']);
    Route::get('/users/{user}', [UserController::class, 'show']);
    Route::put('/users/{user}', [UserController::class, 'update']);
    Route::patch('/users/{user}/toggle-status', [UserController::class, 'toggleStatus']);
    Route::patch('/users/{user}/archive', [UserController::class, 'archive']);
    Route::delete('/users/{user}/soft-delete', [UserController::class, 'softDelete']);
    Route::patch('/users/{user}/restore', [UserController::class, 'restore']);

    // Route::get('/manufacturers/{id}/ivr-form', [ManufacturerController::class, 'downloadForm']);
    Route::post('/management/facilities/clinics/{clinicId}/update', [ClinicController::class, 'updateClinic']);
    Route::put('/management/facilities/clinics/{clinicId}/updatestatus', [ClinicController::class, 'updateClinicStatus']);
    Route::put('/management/facilities/clinics/{clinicId}/deleteclinic', [ClinicController::class, 'deleteClinic']);
    Route::put('/management/facilities/clinics/{clinicId}/archiveclinic', [ClinicController::class, 'archiveClinic']);
    Route::put('/management/facilities/clinics/{clinicId}/unarchiveclinic', [ClinicController::class, 'unarchiveClinic']);

    // IVR
    Route::get('/management/ivr/ivrrequests', [IVRRequestController::class, 'getAllIVRRequests']);
    Route::get('/management/ivr/getbrands', [IVRRequestController::class, 'getAllBrands']);
    Route::get('/management/manufacturer/getmanufacturers', [IVRRequestController::class, 'getAllManufacturers']);
    Route::get('/management/ivr/getclinics', [IVRRequestController::class, 'getAllClinic']);
    Route::get('/management/patients/patientinfo', [IVRRequestController::class, 'getAllPatientInfo']);
    Route::post('/management/add/newivrrequest', [IVRRequestController::class, 'addIVRRequest']);
    Route::post('/management/update/{id}/updateivr', [IVRRequestController::class, 'updateIVRRequest']);
    Route::put('/management/update/{id}/updateivreligiblity', [IVRRequestController::class, 'updateEligibilityStatus']);
    Route::post('/management/update/{id}/updateivrrequest', [IVRRequestController::class, 'updateMagicIVRStatus']);
    Route::put('/management/delete/{id}/deleteivrrequest', [IVRRequestController::class, 'deleteIVRRequest']);
    Route::put('/management/archive/{id}/archiveivrrequest', [IVRRequestController::class, 'archiveIVRRequest']);
    Route::put('/management/archive/{id}/unarchiveivrrequest', [IVRRequestController::class, 'unarchiveIVRRequest']);
    Route::get('/management/ivr/download/{id}/downloadivrform', [IVRRequestController::class, 'downloadIVRForm']);

    // Brand
    Route::get('/management/brands', [BrandController::class, 'getAllBrands']);
    Route::post('/management/brands', [BrandController::class, 'addBrand']);
    Route::post('management/brands/{id}', [BrandController::class, 'updateBrand']);
    Route::get('/management/brands/{id}/archive', [BrandController::class, 'archiveBrand']);
    Route::get('/management/brands/{id}/toggle', [BrandController::class, 'toggleBrandStatus']);
    Route::delete('/management/brands/{id}', [BrandController::class, 'deleteBrand']);

    // Graft Size 
    Route::get('/management/graft-sizes', [GraftSizeController::class, 'getAllGraftSizes']);
    Route::post('/management/graft-sizes', [GraftSizeController::class, 'addNewGraftSize']);
    Route::put('/management/update/{id}/updategraftsize', [GraftSizeController::class, 'updateGraftSize']);
    Route::put('/management/archive/{id}/archivegraftsize', [GraftSizeController::class, 'archiveGraftSize']);
    Route::put('/management/archive/{id}/unarchivegraftsize', [GraftSizeController::class, 'unarchiveGraftSize']);
    Route::put('/management/delete/{id}/deletegraftsize', [GraftSizeController::class, 'deleteGraftSize']);
    Route::put('/management/deactivate/{id}/deactivategraftsize', [GraftSizeController::class, 'toggleInactiveGraftSize']);
    Route::put('/management/activate/{id}/activategraftsize', [GraftSizeController::class, 'activateGraftSize']);
    Route::get('/management/graft-sizes/stats', [GraftSizeController::class, 'getGraftStats']);
    Route::get('/management/graft-sizes/getAllBrands', [GraftSizeController::class, 'getAllBrands']);

    // Order
    Route::get('/management/order/getorders', [OrderController::class, 'getAllOrders']);
    Route::get('/management/order/{id}/getorderbyid', [OrderController::class, 'getAllOrdersById']);
    Route::get('/management/order/getclinics', [OrderController::class, 'getAllClinics']);
    Route::get('/management/order/getbrands', [OrderController::class, 'getAllPatients']);
    Route::get('/management/order/getgraftsizes', [OrderController::class, 'getAllGraftSizes']);
    Route::get('/management/order/users/getpatients', [OrderController::class, 'getAllPatients']);
 
    Route::post('/management/order/add/neworder', [OrderController::class, 'addNewOrder']);
    Route::put('/management/order/update/{id}/updateorder', [OrderController::class, 'updateOrder']);
    Route::put('/management/order/delete/{id}/deleteorder', [OrderController::class, 'deleteOrder']);
    Route::put('/management/order/update/{id}/updateorderstatus', [OrderController::class, 'updateOrderStatus']);
    Route::put('/management/magicorder/update/{id}/updateorderstatus', [OrderController::class, 'updateMagicOrderStatus']);
    Route::post('/management/order/update/{id}/followuporderstatus', [OrderController::class, 'followUpOrder']);
 
    Route::get('/management/manufacturer/order/getmanufacturerorders', [OrderController::class, 'getAllOrdersByManufacturers']);
 
    Route::get('/management/manufacturer/order/getclinicorders', [OrderController::class, 'getAllOrdersByClinics']);
    Route::get('/auth/me-with-clinic', [OrderController::class, 'userWithClinic']);
    Route::post('/management/order/add/neworderbyclinic', [OrderController::class, 'addNewOrderByClinic']);
    Route::put('/management/order/update/{id}/updateorderbyclinic', [OrderController::class, 'updateOrderByClinic']);

    // Inventory & Usage Logs
    Route::get('/inventory/all', [InventoryController::class, 'getInventory']);
    Route::get('/inventory/serial/{serialNumber}', [InventoryController::class, 'getInventoryBySerial']);
    Route::get('/inventory/status/{status}', [InventoryController::class, 'getInventoryByStatus']);
    Route::get('/inventory/search-patients', [InventoryController::class, 'searchPatients']);
    Route::get('/inventory/graft-size/{graftSizeId}', [InventoryController::class, 'getGraftSize']);
    Route::post('/inventory/usage-logs', [InventoryController::class, 'storeUsageLog']);
    Route::put('/inventory/usage-logs/{id}', [InventoryController::class, 'updateUsageLog']);
    Route::patch('/inventory/{id}/status', [InventoryController::class, 'updateInventoryStatus']);
    Route::delete('/inventory/usage-logs/{id}', [InventoryController::class, 'deleteUsageLog']);
});
