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

require __DIR__.'/auth.php';
