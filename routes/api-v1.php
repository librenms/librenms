<?php

use App\Http\Controllers\Api\V1;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API v1 Routes
|--------------------------------------------------------------------------
|
| Served under /api/v1, named api.v1.* and wrapped in the api.v1
| middleware group (see bootstrap/app.php). See doc/API/v1.md.
|
*/

Route::get('health', V1\HealthController::class)->middleware('throttle:60,1')->name('health');

Route::middleware(['auth:sanctum', 'can:api.access'])->group(function (): void {
    Route::get('system', V1\SystemController::class)->name('system');

    Route::apiResource('devices', V1\DeviceController::class)->only(['index', 'show']);
    Route::get('devices/{device}/ports', [V1\PortController::class, 'index'])->name('devices.ports');

    Route::apiResource('ports', V1\PortController::class)->only(['index', 'show']);
    Route::get('ports/{port}/device', [V1\DeviceController::class, 'showForPort'])->name('ports.device');

    Route::prefix('routing')->name('routing.')->group(function (): void {
        Route::apiResource('isis-adjacencies', V1\IsisAdjacencyController::class)
            ->only(['index', 'show'])
            ->parameters(['isis-adjacencies' => 'isisAdjacency']);
        Route::get('isis-adjacencies/{isisAdjacency}/device', [V1\DeviceController::class, 'showForIsisAdjacency'])->name('isis-adjacencies.device');
        Route::get('isis-adjacencies/{isisAdjacency}/port', [V1\PortController::class, 'showForIsisAdjacency'])->name('isis-adjacencies.port');
    });
});
