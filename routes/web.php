<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('devices', 'devices')
    ->middleware(['auth', 'verified'])
    ->name('devices');

Route::view('incidents', 'incidents')
    ->middleware(['auth', 'verified'])
    ->name('incidents');

Route::view('audit-logs', 'audit-logs')
    ->middleware(['auth', 'verified', 'role:Administrador'])
    ->name('audit-logs');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

// Rutas de Reportes (Solo Admin y Operador)
Route::middleware(['auth', 'role:Administrador|Operador'])->group(function () {
    Route::get('/reports/devices/pdf', [\App\Http\Controllers\ReportController::class, 'exportDevicesPDF'])->name('reports.devices.pdf');
    Route::get('/reports/devices/csv', [\App\Http\Controllers\ReportController::class, 'exportDevicesCSV'])->name('reports.devices.csv');
    Route::get('/reports/incidents/pdf', [\App\Http\Controllers\ReportController::class, 'exportIncidentsPDF'])->name('reports.incidents.pdf');
    Route::get('/reports/audit/pdf', [\App\Http\Controllers\ReportController::class, 'exportAuditPDF'])->name('reports.audit.pdf');
    Route::get('/reports/audit/csv', [\App\Http\Controllers\ReportController::class, 'exportAuditCSV'])->name('reports.audit.csv');
});

require __DIR__.'/auth.php';
