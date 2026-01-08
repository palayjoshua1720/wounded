<?php

use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return response()->json([
//         'message' => 'Welcome to the API',
//         'status' => 'success'
//     ]);
// });
Route::fallback(function () {
    return file_get_contents(public_path('index.html'));
});
