<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Auth
Auth::routes();

// WebUI
Route::group(['middleware' => ['auth', '2fa'], 'guard' => 'auth'], function () {
    // Test
    Route::get('/laravel', function () {
        return view('laravel');
    });

    Route::get('locations', 'LocationController@index');

    // old route redirects
    Route::get('poll-log', function () {
        return redirect('pollers/tab=log/');
    });

    // Two Factor Auth
    Route::get('2fa', 'TwoFactorController@showTwoFactorForm')->name('2fa.form');
    Route::post('2fa', 'TwoFactorController@verifyTwoFactor')->name('2fa.verify');
    Route::post('2fa/add', 'TwoFactorController@create');
    Route::post('2fa/cancel', 'TwoFactorController@cancelAdd')->name('2fa.cancel');
    Route::post('2fa/remove', 'TwoFactorController@destroy');

    // Ajax routes
    Route::group(['prefix' => 'ajax'], function () {
        Route::post('set_resolution', 'ResolutionController@set');
        Route::resource('location', 'LocationController', ['only' => ['update', 'destroy']]);

        Route::group(['prefix' => 'form', 'namespace' => 'Form'], function () {
            Route::resource('widget-settings', 'WidgetSettingsController');
        });

        Route::group(['prefix' => 'select', 'namespace' => 'Select'], function () {
            Route::get('application', 'ApplicationController');
            Route::get('bill', 'BillController');
            Route::get('device', 'DeviceController');
            Route::get('device-group', 'DeviceGroupController');
            Route::get('eventlog', 'EventlogController');
            Route::get('graph', 'GraphController');
            Route::get('graph-aggregate', 'GraphAggregateController');
            Route::get('graylog-streams', 'GraylogStreamsController');
            Route::get('syslog', 'SyslogController');
            Route::get('munin', 'MuninPluginController');
            Route::get('port', 'PortController');
            Route::get('port-field', 'PortFieldController');
        });

        Route::group(['prefix' => 'table', 'namespace' => 'Table'], function () {
            Route::post('customers', 'CustomersController');
            Route::post('eventlog', 'EventlogController');
            Route::post('location', 'LocationController');
            Route::post('port-nac', 'PortNacController');
            Route::post('graylog', 'GraylogController');
            Route::post('syslog', 'SyslogController');
        });

        Route::group(['prefix' => 'dash', 'namespace' => 'Widgets'], function () {
            Route::post('alerts', 'AlertsController');
            Route::post('availability-map', 'AvailabilityMapController');
            Route::post('component-status', 'ComponentStatusController');
            Route::post('device-summary-horiz', 'DeviceSummaryHorizController');
            Route::post('device-summary-vert', 'DeviceSummaryVertController');
            Route::post('eventlog', 'EventlogController');
            Route::post('generic-graph', 'GraphController');
            Route::post('generic-image', 'ImageController');
            Route::post('globe', 'GlobeController');
            Route::post('graylog', 'GraylogController');
            Route::post('placeholder', 'PlaceholderController');
            Route::post('notes', 'NotesController');
            Route::post('server-stats', 'ServerStatsController');
            Route::post('syslog', 'SyslogController');
            Route::post('top-devices', 'TopDevicesController');
            Route::post('top-interfaces', 'TopInterfacesController');
            Route::post('worldmap', 'WorldMapController');
        });
    });

    // Debugbar routes need to be here because of catch-all
    if (config('app.env') !== 'production' && config('app.debug') && config('debugbar.enabled') !== false) {
        Route::get('/_debugbar/assets/stylesheets', [
            'as' => 'debugbar-css',
            'uses' => '\Barryvdh\Debugbar\Controllers\AssetController@css'
        ]);

        Route::get('/_debugbar/assets/javascript', [
            'as' => 'debugbar-js',
            'uses' => '\Barryvdh\Debugbar\Controllers\AssetController@js'
        ]);

        Route::get('/_debugbar/open', [
            'as' => 'debugbar-open',
            'uses' => '\Barryvdh\Debugbar\Controllers\OpenController@handler'
        ]);
    }

    // demo helper
    Route::get('demo', function () {
        return redirect('/');
    });

    // Legacy routes
    Route::any('/{path?}', 'LegacyController@index')->where('path', '.*');
});
