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
    Route::get('system', 'LegacyApiController@server_info')->name('server_info');
    Route::middleware(['can:global-read'])->group(function () {
        Route::get('bgp', 'LegacyApiController@list_bgp')->name('list_bgp');

    });
    Route::get('devices', 'LegacyApiController@list_devices')->name('list_devices');
    Route::get('ports', 'LegacyApiController@get_all_ports')->name('get_all_ports');
//    Route::group(['prefix' => 'devices'], function () {
//
//    });
});

// Legacy API
Route::any('/v0/{path?}', 'LegacyController@api')->where('path', '.*');
