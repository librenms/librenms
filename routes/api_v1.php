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
        Route::get('graphs', 'Device\\DeviceGraphController@index')->name('devices.graph.index');
        Route::get('addresses', 'Device\\DeviceAddressController@index')->name('devices.address.index');
        
        Route::get('ports/stack', 'Device\\DevicePortController@stack')->name('devices.port.stack');
        Route::resource('ports', 'Device\\DevicePortController', ['only' => [
            'index',
            'show'
        ]]);
        Route::get('ports/{port}/addresses', 'Device\\DevicePortAddressController@index')->name('devices.port.address.index');

        Route::resource('health', 'Device\\DeviceHealthController', ['only' => [
            'index',
            'show'
        ]]);

        Route::resource('wireless', 'Device\\DeviceWirelessController', ['only' => [
            'index',
            'show'
        ]]);
        
        Route::resource('components', 'Device\\DeviceComponentController', ['except' => [
            'create',
            'edit'
        ]]);

        Route::prefix('logs')->group(function () {
            Route::get('alertlog', 'Device\\DeviceLogController@alertlog')->name('logs.alertlog');
            Route::get('eventlog', 'Device\\DeviceLogController@eventlog')->name('logs.eventlog');
            Route::get('syslog', 'Device\\DeviceLogController@syslog')->name('logs.syslog');
        });

        Route::get('services', 'Device\\DeviceServiceController@index')->name('devices.service.index');
        Route::post('services', 'Device\\DeviceServiceController@store')->name('devices.service.store');
        Route::get('inventory', 'Device\\DeviceInventoryController@index')->name('devices.inventory.index');
        Route::get('vlans', 'Device\\DeviceVlanController@index')->name('devices.vlan.index');

        Route::prefix('routing')->group(function () {
            Route::get('ipsec', 'Device\\DeviceRoutingController@ipsec');
            Route::get('ospf', 'Device\\DeviceRoutingController@ospf');
            Route::get('vrf', 'Device\\DeviceRoutingController@vrf');
            Route::get('bgp', 'Device\\DeviceRoutingController@bgp');
            Route::get('cbgp', 'Device\\DeviceRoutingController@cbgp');
        });
    });
    
    Route::prefix('groups')->group(function () {
        Route::get('devices', 'Group\\GroupDeviceController@index')->name('group.device.index');
        Route::get('devices/{device_group_id}', 'Group\\GroupDeviceController@show')->name('group.device.show');
        // Route::get('ports', 'Group\\GroupPortController@index')->name('group.device.index');
    });

    Route::prefix('logs')->group(function () {
        Route::get('alertlog', 'LogController@alertlog')->name('logs.alertlog');
        Route::get('authlog', 'LogController@authlog')->name('logs.authlog');
        Route::get('eventlog', 'LogController@eventlog')->name('logs.eventlog');
        Route::get('syslog', 'LogController@syslog')->name('logs.syslog');
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

    Route::prefix('resources')->group(function () {
        Route::get('vlans', 'VlanController@index')->name('vlans.index');
        Route::get('networks', 'NetworkController@index');
    });

    Route::prefix('routing')->group(function () {
        Route::get('ipsec', 'RoutingController@ipsec');
        Route::get('ospf', 'RoutingController@ospf');
        Route::get('vrf', 'RoutingController@vrf');
        Route::get('bgp', 'RoutingController@bgp');
        Route::get('cbgp', 'RoutingController@cbgp');
    });

    Route::resource('alerts', 'AlertController', ['except' => [
        'create',
        'edit'
    ]]);

    Route::resource('rules', 'RuleController', ['except' => [
        'create',
        'edit'
    ]]);

    Route::prefix('graphs')->group(function () {
        Route::prefix('devices/{device}')->group(function () {
            // Route::get('health/{class}', 'Graph\\DeviceHealthController@index');
            // Route::get('health/{class}/{id}', 'Graph\\DeviceHealthController@show');
            // Route::get('wireless/{class}', 'Graph\\DeviceWirelessController@index');
            // Route::get('wireless/{class}/{id}', 'Graph\\DeviceWirelessController@show');
        });

        // Route::get('bills', 'Graph\\BillController@');
    });
});
