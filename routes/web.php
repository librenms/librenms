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
Auth::routes(['register' => false, 'reset' => false, 'verify' => false]);

// WebUI
Route::group(['middleware' => ['auth'], 'guard' => 'auth'], function () {

    // pages
    Route::resource('device-groups', 'DeviceGroupController');
    Route::resource('port-groups', 'PortGroupController');
    Route::resource('port', 'PortController', ['only' => 'update']);
    Route::group(['prefix' => 'poller'], function () {
        Route::get('', 'PollerController@pollerTab')->name('poller.index');
        Route::get('log', 'PollerController@logTab')->name('poller.log');
        Route::get('groups', 'PollerController@groupsTab')->name('poller.groups');
        Route::get('settings', 'PollerController@settingsTab')->name('poller.settings');
        Route::get('performance', 'PollerController@performanceTab')->name('poller.performance');
        Route::resource('{id}/settings', 'PollerSettingsController', ['as' => 'poller'])->only(['update', 'destroy']);
    });
    Route::prefix('services')->name('services.')->group(function () {
        Route::resource('templates', 'ServiceTemplateController');
        Route::post('templates/applyAll', 'ServiceTemplateController@applyAll')->name('templates.applyAll');
        Route::post('templates/apply/{template}', 'ServiceTemplateController@apply')->name('templates.apply');
        Route::post('templates/remove/{template}', 'ServiceTemplateController@remove')->name('templates.remove');
    });
    Route::get('locations', 'LocationController@index');
    Route::resource('preferences', 'UserPreferencesController', ['only' => ['index', 'store']]);
    Route::resource('users', 'UserController');
    Route::get('about', 'AboutController@index');
    Route::get('authlog', 'UserController@authlog');
    Route::get('overview', 'OverviewController@index')->name('overview');
    Route::get('/', 'OverviewController@index')->name('home');
    Route::view('vminfo', 'vminfo');

    // Device Tabs
    Route::group(['prefix' => 'device/{device}', 'namespace' => 'Device\Tabs', 'as' => 'device.'], function () {
        Route::put('notes', 'NotesController@update')->name('notes.update');
    });

    Route::match(['get', 'post'], 'device/{device}/{tab?}/{vars?}', 'DeviceController@index')
        ->name('device')->where(['vars' => '.*']);

    // Maps
    Route::group(['prefix' => 'maps', 'namespace' => 'Maps'], function () {
        Route::get('devicedependency', 'DeviceDependencyController@dependencyMap');
    });

    // admin pages
    Route::group(['middleware' => ['can:admin']], function () {
        Route::get('settings/{tab?}/{section?}', 'SettingsController@index')->name('settings');
        Route::put('settings/{name}', 'SettingsController@update')->name('settings.update');
        Route::delete('settings/{name}', 'SettingsController@destroy')->name('settings.destroy');
    });

    // old route redirects
    Route::permanentRedirect('poll-log', 'poller/log');

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
        Route::resource('pollergroup', 'PollerGroupController', ['only' => ['destroy']]);
        // misc ajax controllers
        Route::group(['namespace' => 'Ajax'], function () {
            Route::post('set_map_group', 'AvailabilityMapController@setGroup');
            Route::post('set_map_view', 'AvailabilityMapController@setView');
            Route::post('set_resolution', 'ResolutionController@set');
            Route::get('netcmd', 'NetCommand@run');
            Route::post('ripe/raw', 'RipeNccApiController@raw');
        });

        Route::get('settings/list', 'SettingsController@listAll')->name('settings.list');

        // form ajax handlers, perhaps should just be page controllers
        Route::group(['prefix' => 'form', 'namespace' => 'Form'], function () {
            Route::resource('widget-settings', 'WidgetSettingsController');
            Route::post('copy-dashboard', 'CopyDashboardController@store');
        });

        // js select2 data controllers
        Route::group(['prefix' => 'select', 'namespace' => 'Select'], function () {
            Route::get('application', 'ApplicationController');
            Route::get('bill', 'BillController');
            Route::get('dashboard', 'DashboardController')->name('ajax.select.dashboard');
            Route::get('device', 'DeviceController');
            Route::get('device-field', 'DeviceFieldController');
            Route::get('device-group', 'DeviceGroupController');
            Route::get('port-group', 'PortGroupController');
            Route::get('eventlog', 'EventlogController');
            Route::get('graph', 'GraphController');
            Route::get('graph-aggregate', 'GraphAggregateController');
            Route::get('graylog-streams', 'GraylogStreamsController');
            Route::get('syslog', 'SyslogController');
            Route::get('location', 'LocationController');
            Route::get('munin', 'MuninPluginController');
            Route::get('service', 'ServiceController');
            Route::get('template', 'ServiceTemplateController');
            Route::get('port', 'PortController');
            Route::get('port-field', 'PortFieldController');
        });

        // jquery bootgrid data controllers
        Route::group(['prefix' => 'table', 'namespace' => 'Table'], function () {
            Route::post('alert-schedule', 'AlertScheduleController');
            Route::post('customers', 'CustomersController');
            Route::post('device', 'DeviceController');
            Route::post('edit-ports', 'EditPortsController');
            Route::post('eventlog', 'EventlogController');
            Route::post('fdb-tables', 'FdbTablesController');
            Route::post('graylog', 'GraylogController');
            Route::post('location', 'LocationController');
            Route::post('mempools', 'MempoolsController');
            Route::post('outages', 'OutagesController');
            Route::post('port-nac', 'PortNacController');
            Route::post('routes', 'RoutesTablesController');
            Route::post('syslog', 'SyslogController');
            Route::post('vminfo', 'VminfoController');
        });

        // dashboard widgets
        Route::group(['prefix' => 'dash', 'namespace' => 'Widgets'], function () {
            Route::post('alerts', 'AlertsController');
            Route::post('alertlog', 'AlertlogController');
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
            Route::post('top-errors', 'TopErrorsController');
            Route::post('worldmap', 'WorldMapController');
            Route::post('alertlog-stats', 'AlertlogStatsController');
        });
    });

    // demo helper
    Route::permanentRedirect('demo', '/');
});

// installation routes
Route::group(['prefix' => 'install', 'namespace' => 'Install'], function () {
    Route::get('/', 'InstallationController@redirectToFirst')->name('install');
    Route::get('/checks', 'ChecksController@index')->name('install.checks');
    Route::get('/database', 'DatabaseController@index')->name('install.database');
    Route::get('/user', 'MakeUserController@index')->name('install.user');
    Route::get('/finish', 'FinalizeController@index')->name('install.finish');

    Route::post('/user/create', 'MakeUserController@create')->name('install.action.user');
    Route::post('/database/test', 'DatabaseController@test')->name('install.acton.test-database');
    Route::get('/ajax/database/migrate', 'DatabaseController@migrate')->name('install.action.migrate');
    Route::get('/ajax/steps', 'InstallationController@stepsCompleted')->name('install.action.steps');
    Route::any('{path?}', 'InstallationController@invalid')->where('path', '.*'); // 404
});

// Legacy routes
Route::any('/dummy_legacy_auth/{path?}', 'LegacyController@dummy')->middleware('auth');
Route::any('/dummy_legacy_unauth/{path?}', 'LegacyController@dummy');
Route::any('/{path?}', 'LegacyController@index')
    ->where('path', '^((?!_debugbar).)*')
    ->middleware('auth');
