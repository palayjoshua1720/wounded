<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\SampleController;

// System Info
Route::get('/version', function (Request $request) {
    return response()->json([
        'laravel_version' => app()->version(),
        'php_version' => phpversion(),
    ]);
});

// Sample Routes
Route::get('/sample', [SampleController::class, 'index']);