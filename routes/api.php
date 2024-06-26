<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\ApiController;
use ErlandMuchasaj\LaravelGzip\Middleware\GzipEncodeResponse;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/machine-on', [ApiController::class, 'isMachineOn']);
Route::get('/reports-datatables', [ApiController::class, 'reportDatatables']);
Route::get('/registers-events', [ApiController::class, 'getRegistersByEvents']);
Route::get('/registers-details/{id}/{type}', [ApiController::class, 'eventDetails']);
Route::get('/mensagens-um-valor', [ApiController::class, 'getMessageOneValue']);

Route::middleware([GzipEncodeResponse::class])->group(function () {
    Route::get('/get-events', [ApiController::class, 'getEvents']);
    Route::get('/get-events-ten-attributes', [ApiController::class, 'getEventsTenAttributes']);
});
