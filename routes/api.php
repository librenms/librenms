<?php

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/



Route::group(['prefix' => 'v0', 'namespace' => '\App\Api\Controllers'], function () {
    Route::get('devices', 'LegacyApiController@list_devices')->name('list_devices');
//    Route::group(['prefix' => 'devices'], function () {
//
//    });
});

// Legacy API
Route::any('/v0/{path?}', 'LegacyController@api')->where('path', '.*');
