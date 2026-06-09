<?php

use Illuminate\Http\Request;
use App\Http\Controllers\OcrController;
use Illuminate\Support\Facades\Route;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');


Route::post('/ocr/scan', [OcrController::class, 'process']);