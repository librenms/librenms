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

    // pages
    Route::resource('device-groups', 'DeviceGroupController');
    Route::get('locations', 'LocationController@index');
    Route::resource('preferences', 'UserPreferencesController', ['only' => ['index', 'store']]);
    Route::resource('users', 'UserController');
    Route::get('about', 'AboutController@index');
    Route::get('authlog', 'UserController@authlog');

    // old route redirects
    Route::permanentRedirect('poll-log', 'pollers/tab=log/');

    // Two Factor Auth
    Route::group(['prefix' => '2fa', 'namespace' => 'Auth'], function () {
        Route::get('', 'TwoFactorController@showTwoFactorForm')->name('2fa.form');
        Route::post('', 'TwoFactorController@verifyTwoFactor')->name('2fa.verify');
        Route::post('add', 'TwoFactorController@create')->name('2fa.add');
        Route::post('cancel', 'TwoFactorController@cancelAdd')->name('2fa.cancel');
        Route::post('remove', 'TwoFactorController@destroy')->name('2fa.remove');

        Route::post('{user}/unlock', 'TwoFactorManagementController@unlock')->name('2fa.unlock');
        Route::delete('{user}', 'TwoFactorManagementController@destroy')->name('2fa.delete');
    });

    // Ajax routes
    Route::group(['prefix' => 'ajax'], function () {
        // page ajax controllers
        Route::resource('location', 'LocationController', ['only' => ['update', 'destroy']]);

        // misc ajax controllers
        Route::group(['namespace' => 'Ajax'], function () {
            Route::post('set_resolution', 'ResolutionController@set');
            Route::get('netcmd', 'NetCommand@run');
            Route::post('ripe/raw', 'RipeNccApiController@raw');
        });

        // form ajax handlers, perhaps should just be page controllers
        Route::group(['prefix' => 'form', 'namespace' => 'Form'], function () {
            Route::resource('widget-settings', 'WidgetSettingsController');
        });

        // js select2 data controllers
        Route::group(['prefix' => 'select', 'namespace' => 'Select'], function () {
            Route::get('application', 'ApplicationController');
            Route::get('bill', 'BillController');
            Route::get('device', 'DeviceController');
            Route::get('device-field', 'DeviceFieldController');
            Route::get('device-group', 'DeviceGroupController');
            Route::get('eventlog', 'EventlogController');
            Route::get('graph', 'GraphController');
            Route::get('graph-aggregate', 'GraphAggregateController');
            Route::get('graylog-streams', 'GraylogStreamsController');
            Route::get('syslog', 'SyslogController');
            Route::get('location', 'LocationController');
            Route::get('munin', 'MuninPluginController');
            Route::get('service', 'ServiceController');
            Route::get('port', 'PortController');
            Route::get('port-field', 'PortFieldController');
        });

        // jquery bootgrid data controllers
        Route::group(['prefix' => 'table', 'namespace' => 'Table'], function () {
            Route::post('customers', 'CustomersController');
            Route::post('device', 'DeviceController');
            Route::post('eventlog', 'EventlogController');
            Route::post('fdb-tables', 'FdbTablesController');
            Route::post('graylog', 'GraylogController');
            Route::post('location', 'LocationController');
            Route::post('port-nac', 'PortNacController');
            Route::post('syslog', 'SyslogController');
        });

        // dashboard widgets
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

    // demo helper
    Route::permanentRedirect('demo', '/');

    // blank page, dummy page for external code using Laravel::bootWeb()
    Route::any('/blank', function () {
        return '';
    });

    // Legacy routes
    Route::any('/{path?}', 'LegacyController@index')
        ->where('path', '^((?!_debugbar).)*');
});
