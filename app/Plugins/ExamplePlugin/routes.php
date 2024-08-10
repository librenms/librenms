<?php

use App\Plugins\ExamplePlugin\ExamplePluginImageController;
use Illuminate\Support\Facades\Route;

Route::prefix('examplePlugin')->middleware(['web'])->namespace('\App\Plugins\ExamplePlugin')->group(function () {
    Route::get('image/{file}', [ExamplePluginImageController::class, 'image'])->name('examplePluginImage');
});
