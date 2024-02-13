<?php

use App\Http\Controllers\AboutController;
use App\Http\Controllers\AlertController;
use App\Http\Controllers\AlertTransportController;
use App\Http\Controllers\Auth\SocialiteController;
use App\Http\Controllers\Maps\CustomMapBackgroundController;
use App\Http\Controllers\Maps\CustomMapController;
use App\Http\Controllers\Maps\CustomMapDataController;
use App\Http\Controllers\Maps\DeviceDependencyController;
use App\Http\Controllers\PushNotificationController;
use App\Http\Controllers\ValidateController;
use App\Http\Middleware\AuthenticateGraph;
use Illuminate\Support\Facades\Route;

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

// Socialite
Route::prefix('auth')->name('socialite.')->group(function () {
    Route::post('{provider}/redirect', [SocialiteController::class, 'redirect'])->name('redirect');
    Route::match(['get', 'post'], '{provider}/callback', [SocialiteController::class, 'callback'])->name('callback');
    Route::get('{provider}/metadata', [SocialiteController::class, 'metadata'])->name('metadata');
});

Route::get('graph/{path?}', GraphController::class)
    ->where('path', '.*')
    ->middleware(['web', AuthenticateGraph::class])->name('graph');

// WebUI
Route::middleware(['auth'])->group(function () {
    // pages
    Route::post('alert/{alert}/ack', [AlertController::class, 'ack'])->name('alert.ack');
    Route::resource('device-groups', 'DeviceGroupController');
    Route::any('inventory', \App\Http\Controllers\InventoryController::class)->name('inventory');
    Route::get('inventory/purge', [\App\Http\Controllers\InventoryController::class, 'purge'])->name('inventory.purge');
    Route::resource('port', 'PortController')->only('update');
    Route::prefix('poller')->group(function () {
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
    Route::resource('preferences', 'UserPreferencesController')->only('index', 'store');
    Route::resource('users', 'UserController');
    Route::get('about', [AboutController::class, 'index'])->name('about');
    Route::delete('reporting', [AboutController::class, 'clearReportingData'])->name('reporting.clear');
    Route::get('authlog', 'UserController@authlog');
    Route::get('overview', 'OverviewController@index')->name('overview');
    Route::get('/', 'OverviewController@index')->name('home');
    Route::view('vminfo', 'vminfo');

    Route::get('nac', 'NacController@index');

    // Device Tabs
    Route::prefix('device/{device}')->namespace('Device\Tabs')->name('device.')->group(function () {
        Route::put('notes', 'NotesController@update')->name('notes.update');
    });

    Route::match(['get', 'post'], 'device/{device}/{tab?}/{vars?}', 'DeviceController@index')
        ->name('device')->where('vars', '.*');

    // Maps
    Route::prefix('maps')->group(function () {
        Route::resource('custom', CustomMapController::class, ['as' => 'maps'])
            ->parameters(['custom' => 'map'])->except('create');
        Route::get('custom/{map}/background', [CustomMapBackgroundController::class, 'get'])->name('maps.custom.background');
        Route::post('custom/{map}/background', [CustomMapBackgroundController::class, 'save'])->name('maps.custom.background.save');
        Route::get('custom/{map}/data', [CustomMapDataController::class, 'get'])->name('maps.custom.data');
        Route::post('custom/{map}/data', [CustomMapDataController::class, 'save'])->name('maps.custom.data.save');
    });
    Route::get('maps/devicedependency', [DeviceDependencyController::class, 'dependencyMap']);

    // dashboard
    Route::resource('dashboard', 'DashboardController')->except(['create', 'edit']);
    Route::post('dashboard/{dashboard}/copy', 'DashboardController@copy')->name('dashboard.copy');
    Route::post('dashboard/{dashboard}/widgets', 'DashboardWidgetController@add')->name('dashboard.widget.add');
    Route::delete('dashboard/{dashboard}/widgets', 'DashboardWidgetController@clear')->name('dashboard.widget.clear');
    Route::put('dashboard/{dashboard}/widgets', 'DashboardWidgetController@update')->name('dashboard.widget.update');
    Route::delete('dashboard/widgets/{widget}', 'DashboardWidgetController@remove')->name('dashboard.widget.remove');
    Route::put('dashboard/widgets/{widget}', 'WidgetSettingsController@update')->name('dashboard.widget.settings');

    // Push notifications
    Route::prefix('push')->group(function () {
        Route::get('token', [PushNotificationController::class, 'token'])->name('push.token');
        Route::get('key', [PushNotificationController::class, 'key'])->name('push.key');
        Route::post('register', [PushNotificationController::class, 'register'])->name('push.register');
        Route::post('unregister', [PushNotificationController::class, 'unregister'])->name('push.unregister');
    });

    // admin pages
    Route::middleware('can:admin')->group(function () {
        Route::get('settings/{tab?}/{section?}', 'SettingsController@index')->name('settings');
        Route::put('settings/{name}', 'SettingsController@update')->name('settings.update');
        Route::delete('settings/{name}', 'SettingsController@destroy')->name('settings.destroy');

        Route::post('alert/transports/{transport}/test', [AlertTransportController::class, 'test'])->name('alert.transports.test');

        Route::get('plugin/settings', PluginAdminController::class)->name('plugin.admin');
        Route::get('plugin/settings/{plugin:plugin_name}', PluginSettingsController::class)->name('plugin.settings');
        Route::post('plugin/settings/{plugin:plugin_name}', 'PluginSettingsController@update')->name('plugin.update');

        Route::resource('port-groups', 'PortGroupController');
        Route::get('validate', [ValidateController::class, 'index'])->name('validate');
        Route::get('validate/results', [ValidateController::class, 'runValidation'])->name('validate.results');
        Route::post('validate/fix', [ValidateController::class, 'runFixer'])->name('validate.fix');
    });

    Route::get('plugin', 'PluginLegacyController@redirect');
    Route::redirect('plugin/view=admin', '/plugin/admin');
    Route::get('plugin/p={pluginName}', 'PluginLegacyController@redirect');
    Route::any('plugin/v1/{plugin:plugin_name}/{other?}', PluginLegacyController::class)->where('other', '(.*)')->name('plugin.legacy');
    Route::get('plugin/{plugin:plugin_name}', PluginPageController::class)->name('plugin.page');

    // old route redirects
    Route::permanentRedirect('poll-log', 'poller/log');

    // Two Factor Auth
    Route::prefix('2fa')->namespace('Auth')->group(function () {
        Route::get('', 'TwoFactorController@showTwoFactorForm')->name('2fa.form');
        Route::post('', 'TwoFactorController@verifyTwoFactor')->name('2fa.verify');
        Route::post('add', 'TwoFactorController@create')->name('2fa.add');
        Route::post('cancel', 'TwoFactorController@cancelAdd')->name('2fa.cancel');
        Route::post('remove', 'TwoFactorController@destroy')->name('2fa.remove');

        Route::post('{user}/unlock', 'TwoFactorManagementController@unlock')->name('2fa.unlock');
        Route::delete('{user}', 'TwoFactorManagementController@destroy')->name('2fa.delete');
    });

    // Ajax routes
    Route::prefix('ajax')->group(function () {
        // page ajax controllers
        Route::resource('location', 'LocationController')->only('update', 'destroy');
        Route::resource('pollergroup', 'PollerGroupController')->only('destroy');
        // misc ajax controllers
        Route::namespace('Ajax')->group(function () {
            Route::get('search/bgp', BgpSearchController::class);
            Route::get('search/device', DeviceSearchController::class);
            Route::get('search/port', PortSearchController::class);
            Route::post('set_map_group', 'AvailabilityMapController@setGroup');
            Route::post('set_map_view', 'AvailabilityMapController@setView');
            Route::post('set_resolution', 'ResolutionController@set');
            Route::get('netcmd', 'NetCommand@run');
            Route::post('ripe/raw', 'RipeNccApiController@raw');
            Route::get('snmp/capabilities', 'SnmpCapabilities')->name('snmp.capabilities');
        });

        Route::get('settings/list', 'SettingsController@listAll')->name('settings.list');

        // js select2 data controllers
        Route::prefix('select')->namespace('Select')->group(function () {
            Route::get('application', 'ApplicationController')->name('ajax.select.application');
            Route::get('bill', 'BillController')->name('ajax.select.bill');
            Route::get('dashboard', 'DashboardController')->name('ajax.select.dashboard');
            Route::get('device', 'DeviceController')->name('ajax.select.device');
            Route::get('device-field', 'DeviceFieldController')->name('ajax.select.device-field');
            Route::get('device-group', 'DeviceGroupController')->name('ajax.select.device-group');
            Route::get('port-group', 'PortGroupController')->name('ajax.select.port-group');
            Route::get('role', 'RoleController')->name('ajax.select.role');
            Route::get('eventlog', 'EventlogController')->name('ajax.select.eventlog');
            Route::get('graph', 'GraphController')->name('ajax.select.graph');
            Route::get('graph-aggregate', 'GraphAggregateController')->name('ajax.select.graph-aggregate');
            Route::get('graylog-streams', 'GraylogStreamsController')->name('ajax.select.graylog-streams');
            Route::get('inventory', 'InventoryController')->name('ajax.select.inventory');
            Route::get('syslog', 'SyslogController')->name('ajax.select.syslog');
            Route::get('location', 'LocationController')->name('ajax.select.location');
            Route::get('munin', 'MuninPluginController')->name('ajax.select.munin');
            Route::get('role', 'RoleController')->name('ajax.select.role');
            Route::get('service', 'ServiceController')->name('ajax.select.service');
            Route::get('template', 'ServiceTemplateController')->name('ajax.select.template');
            Route::get('poller-group', 'PollerGroupController')->name('ajax.select.poller-group');
            Route::get('port', 'PortController')->name('ajax.select.port');
            Route::get('port-field', 'PortFieldController')->name('ajax.select.port-field');
        });

        // jquery bootgrid data controllers
        Route::prefix('table')->namespace('Table')->group(function () {
            Route::post('alert-schedule', 'AlertScheduleController');
            Route::post('customers', 'CustomersController');
            Route::post('device', 'DeviceController');
            Route::post('edit-ports', 'EditPortsController');
            Route::post('eventlog', 'EventlogController');
            Route::post('fdb-tables', 'FdbTablesController');
            Route::post('graylog', 'GraylogController');
            Route::post('inventory', 'InventoryController')->name('table.inventory');
            Route::post('location', 'LocationController');
            Route::post('mempools', 'MempoolsController');
            Route::post('outages', 'OutagesController');
            Route::post('port-nac', 'PortNacController')->name('table.port-nac');
            Route::post('port-stp', 'PortStpController');
            Route::post('ports', 'PortsController')->name('table.ports');
            Route::post('routes', 'RoutesTablesController');
            Route::post('syslog', 'SyslogController');
            Route::post('tnmsne', 'TnmsneController')->name('table.tnmsne');
            Route::post('vminfo', 'VminfoController');
        });

        // dashboard widgets
        Route::prefix('dash')->namespace('Widgets')->group(function () {
            Route::post('alerts', 'AlertsController');
            Route::post('alertlog', 'AlertlogController');
            Route::post('availability-map', 'AvailabilityMapController');
            Route::post('component-status', 'ComponentStatusController');
            Route::post('device-summary-horiz', 'DeviceSummaryHorizController');
            Route::post('device-summary-vert', 'DeviceSummaryVertController');
            Route::post('device-types', 'DeviceTypeController');
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

// routes that don't need authentication
Route::group(['prefix' => 'ajax', 'namespace' => 'Ajax'], function () {
    Route::post('set_timezone', 'TimezoneController@set');
});

// installation routes
Route::prefix('install')->namespace('Install')->group(function () {
    Route::get('/', 'InstallationController@redirectToFirst')->name('install');
    Route::get('/checks', 'ChecksController@index')->name('install.checks');
    Route::get('/database', 'DatabaseController@index')->name('install.database');
    Route::get('/user', 'MakeUserController@index')->name('install.user');
    Route::get('/finish', 'FinalizeController@index')->name('install.finish');

    Route::post('/finish', 'FinalizeController@saveConfig')->name('install.finish.save');
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
