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

Route::namespace('Api')->group(function () {
    Route::resource('devices', 'Device\\DeviceController', ['except' => [
        'create',
        'edit'
    ]]);
    
    Route::prefix('devices/{device}')->group(function () {
        Route::get('graphs', 'Device\\DeviceGraphController@index')->name('device.graph.index');
        Route::get('addresses', 'Device\\DeviceAddressController@index')->name('device.address.index');

        Route::get('ports/stack', 'Device\\DevicePortController@stack')->name('device.port.stack');
        Route::resource('ports', 'Device\\DevicePortController', ['only' => [
            'index',
            'show'
        ]]);
        Route::get('ports/{port}/addresses', 'Device\\DevicePortAddressController@index')->name('device.port.address.index');

        Route::resource('health', 'Device\\DeviceHealthController', ['only' => [
            'index',
            'show'
        ]]);

        Route::resource('wireless', 'Device\\DeviceWirelessController', ['only' => [
            'index',
            'show'
        ]]);
        
        Route::get('services', 'Device\\DeviceServiceController@index')->name('device.service.index');
        Route::post('services', 'Device\\DeviceServiceController@store')->name('device.service.store');
    });
    
    Route::prefix('groups')->group(function () {
        Route::get('devices', 'Group\\GroupDeviceController@index')->name('group.device.index');
        Route::get('devices/{device_group_id}', 'Group\\GroupDeviceController@show')->name('group.device.show');
        Route::get('ports', 'Group\\GroupPortController@index')->name('group.device.index');
    });

    Route::resource('ports', 'PortController', ['only' => [
        'index',
        'show'
    ]]);

    Route::resource('bills', 'Bill\\BillController', ['except' => [
        'create',
        'edit'
    ]]);

    Route::get('services', 'ServiceController@index')->name('device.service.index');

    Route::get('bills/{bill}/history', 'Bill\\BillHistoryController@index')->name('bill.history.index');

    Route::get('inventory', 'InventoryController@index')->name('inventory.index');

    Route::prefix('graphs')->group(function () {
        Route::prefix('devices/{device}')->group(function () {
            Route::get('health/{class}', 'Graph\\DeviceHealthController@index');
            Route::get('health/{class}/{id}', 'Graph\\DeviceHealthController@show');
            Route::get('wireless/{class}', 'Graph\\DeviceWirelessController@index');
            Route::get('wireless/{class}/{id}', 'Graph\\DeviceWirelessController@show');
        });

        // Route::get('bills', 'Graph\\BillController@');
    });
});
