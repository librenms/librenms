<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API V1 Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// TODO: Protect with permission and AUTH middleware

Route::namespace('Api')->group(function() {
    Route::resource('devices', 'Device\\DeviceController', [ 'except' => [
        'create',
        'edit'
    ]]);
    
    Route::prefix('devices/{device}')->group(function() {
        Route::get('health', 'Device\\DeviceHealthController@index')->name('device.health.index');
        Route::get('health/{class}', 'Device\\DeviceHealthController@show')->name('device.health.sensor.show');

        Route::get('wireless', 'Device\\DeviceWirelessController@index')->name('device.wireless.index');
        Route::get('wireless/{class}', 'Device\\DeviceWirelessController@show')->name('device.wireless.show');

        Route::resource('ports', 'Device\\DevicePortController', [ 'only' => [
            'index',
            'show'
        ]]);
    });
    
    Route::resource('ports', 'PortController', [ 'only' => [
        'index',
        'show'
    ]]);

    Route::resource('bills', 'BillController', [ 'only' => [
        'index',
        'show'
    ]]);

    Route::prefix('graphs')->group(function() {
        Route::get('devices/{device}/health/{class}', 'Graph\\DeviceHealthController@index');
        Route::get('devices/{device}/health/{class}/{id}', 'Graph\\DeviceHealthController@show');
        Route::get('devices/{device}/wireless/{class}', 'Graph\\DeviceWirelessController@index');
        Route::get('devices/{device}/wireless/{class}/{id}', 'Graph\\DeviceWirelessController@show');

        Route::get('bills', 'Graph\\BillController@');
    });
});

