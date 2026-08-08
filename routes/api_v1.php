<?php

use App\Http\Controllers\Api\V1\HealthController;
use App\Http\Controllers\Api\V1\PingController;
use Illuminate\Support\Facades\Route;

// Public, unauthenticated liveness probe.
Route::get('ping', PingController::class)->name('v1.ping');

// Health check requires authentication (exposes subsystem status).
Route::get('health', HealthController::class)
    ->middleware('auth:sanctum')
    ->name('v1.health');
