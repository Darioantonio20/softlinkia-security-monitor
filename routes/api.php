<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\EventController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Endpoint de Simulación Externa (Módulo 3)
Route::post('/simulate-event', [EventController::class, 'simulate']);

// Recursos REST (Extras valorados)
Route::get('/devices', [\App\Http\Controllers\Api\DeviceController::class, 'index']);
Route::get('/incidents', [\App\Http\Controllers\Api\IncidentController::class, 'index']);
