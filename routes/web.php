<?php

use App\Http\Controllers\DeviceGroupController;
use App\Http\Controllers\GraphController;
use App\Http\Controllers\PluginAdminController;
use App\Http\Controllers\PluginPageController;
use App\Http\Controllers\PollerGroupController;
use App\Http\Controllers\PollerSettingsController;
use App\Http\Controllers\PortController;
use App\Http\Controllers\PortGroupController;
use App\Http\Controllers\Select;
use App\Http\Controllers\Table;
use App\Http\Controllers\UserPreferencesController;
use App\Http\Controllers\Widgets;
use App\Http\Controllers\AboutController;
use App\Http\Controllers\Ajax;
use App\Http\Controllers\Auth;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DashboardWidgetController;
use App\Http\Controllers\Device;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\Install;
use App\Http\Controllers\LegacyController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\Maps;
use App\Http\Controllers\OverviewController;
use App\Http\Controllers\PluginLegacyController;
use App\Http\Controllers\PluginSettingsController;
use App\Http\Controllers\PollerController;
use App\Http\Controllers\ServiceTemplateController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WidgetSettingsController;
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
    Route::post('{provider}/redirect', [\App\Http\Controllers\Auth\SocialiteController::class, 'redirect'])->name('redirect');
    Route::match(['get', 'post'], '{provider}/callback', [\App\Http\Controllers\Auth\SocialiteController::class, 'callback'])->name('callback');
    Route::get('{provider}/metadata', [\App\Http\Controllers\Auth\SocialiteController::class, 'metadata'])->name('metadata');
});

Route::get('graph/{path?}', GraphController::class)
    ->where('path', '.*')
    ->middleware(['web', \App\Http\Middleware\AuthenticateGraph::class])->name('graph');

// WebUI
Route::middleware('auth')->group([ 'guard' => 'auth'], function () {

    // pages
    Route::post('alert/{alert}/ack', [\App\Http\Controllers\AlertController::class, 'ack'])->name('alert.ack');
    Route::resource('device-groups', DeviceGroupController::class);
    Route::resource('port', PortController::class)->only('update');
    Route::prefix('poller')->group(function () {
        Route::get('', [PollerController::class, 'pollerTab'])->name('poller.index');
        Route::get('log', [PollerController::class, 'logTab'])->name('poller.log');
        Route::get('groups', [PollerController::class, 'groupsTab'])->name('poller.groups');
        Route::get('settings', [PollerController::class, 'settingsTab'])->name('poller.settings');
        Route::get('performance', [PollerController::class, 'performanceTab'])->name('poller.performance');
        Route::resource('{id}/settings', PollerSettingsController::class, ['as' => 'poller'])->only(['update', 'destroy']);
    });
    Route::prefix('services')->name('services.')->group(function () {
        Route::resource('templates', ServiceTemplateController::class);
        Route::post('templates/applyAll', [ServiceTemplateController::class, 'applyAll'])->name('templates.applyAll');
        Route::post('templates/apply/{template}', [ServiceTemplateController::class, 'apply'])->name('templates.apply');
        Route::post('templates/remove/{template}', [ServiceTemplateController::class, 'remove'])->name('templates.remove');
    });
    Route::get('locations', [LocationController::class, 'index']);
    Route::resource('preferences', UserPreferencesController::class)->only('index', 'store');
    Route::resource('users', UserController::class);
    Route::get('about', [AboutController::class, 'index']);
    Route::get('authlog', [UserController::class, 'authlog']);
    Route::get('overview', [OverviewController::class, 'index'])->name('overview');
    Route::get('/', [OverviewController::class, 'index'])->name('home');
    Route::view('vminfo', 'vminfo');

    // Device Tabs
    Route::prefix('device/{device}')->name('device.')->group(function () {
        Route::put('notes', [Device\Tabs\NotesController::class, 'update'])->name('notes.update');
    });

    Route::match(['get', 'post'], 'device/{device}/{tab?}/{vars?}', [DeviceController::class, 'index'])
        ->name('device')->where(['vars' => '.*']);

    // Maps
    Route::prefix('maps')->group(function () {
        Route::get('devicedependency', [Maps\DeviceDependencyController::class, 'dependencyMap']);
    });

    // dashboard
    Route::resource('dashboard', DashboardController::class)->except(['create', 'edit']);
    Route::post('dashboard/{dashboard}/copy', [DashboardController::class, 'copy'])->name('dashboard.copy');
    Route::post('dashboard/{dashboard}/widgets', [DashboardWidgetController::class, 'add'])->name('dashboard.widget.add');
    Route::delete('dashboard/{dashboard}/widgets', [DashboardWidgetController::class, 'clear'])->name('dashboard.widget.clear');
    Route::put('dashboard/{dashboard}/widgets', [DashboardWidgetController::class, 'update'])->name('dashboard.widget.update');
    Route::delete('dashboard/widgets/{widget}', [DashboardWidgetController::class, 'remove'])->name('dashboard.widget.remove');
    Route::put('dashboard/widgets/{widget}', [WidgetSettingsController::class, 'update'])->name('dashboard.widget.settings');

    // Push notifications
    Route::prefix('push')->group(function () {
        Route::get('token', [\App\Http\Controllers\PushNotificationController::class, 'token'])->name('push.token');
        Route::get('key', [\App\Http\Controllers\PushNotificationController::class, 'key'])->name('push.key');
        Route::post('register', [\App\Http\Controllers\PushNotificationController::class, 'register'])->name('push.register');
        Route::post('unregister', [\App\Http\Controllers\PushNotificationController::class, 'unregister'])->name('push.unregister');
    });

    // admin pages
    Route::middleware('can:admin')->group(function () {
        Route::get('settings/{tab?}/{section?}', [SettingsController::class, 'index'])->name('settings');
        Route::put('settings/{name}', [SettingsController::class, 'update'])->name('settings.update');
        Route::delete('settings/{name}', [SettingsController::class, 'destroy'])->name('settings.destroy');

        Route::post('alert/transports/{transport}/test', [\App\Http\Controllers\AlertTransportController::class, 'test'])->name('alert.transports.test');

        Route::get('plugin/settings', PluginAdminController::class)->name('plugin.admin');
        Route::get('plugin/settings/{plugin:plugin_name}', PluginSettingsController::class)->name('plugin.settings');
        Route::post('plugin/settings/{plugin:plugin_name}', [PluginSettingsController::class, 'update'])->name('plugin.update');

        Route::resource('port-groups', PortGroupController::class);
        Route::get('validate', [\App\Http\Controllers\ValidateController::class, 'index'])->name('validate');
        Route::get('validate/results', [\App\Http\Controllers\ValidateController::class, 'runValidation'])->name('validate.results');
        Route::post('validate/fix', [\App\Http\Controllers\ValidateController::class, 'runFixer'])->name('validate.fix');
    });

    Route::get('plugin', [PluginLegacyController::class, 'redirect']);
    Route::redirect('plugin/view=admin', '/plugin/admin');
    Route::get('plugin/p={pluginName}', [PluginLegacyController::class, 'redirect']);
    Route::any('plugin/v1/{plugin:plugin_name}/{other?}', PluginLegacyController::class)->where('other', '(.*)')->name('plugin.legacy');
    Route::get('plugin/{plugin:plugin_name}', PluginPageController::class)->name('plugin.page');

    // old route redirects
    Route::permanentRedirect('poll-log', 'poller/log');

    // Two Factor Auth
    Route::prefix('2fa')->group(function () {
        Route::get('', [Auth\TwoFactorController::class, 'showTwoFactorForm'])->name('2fa.form');
        Route::post('', [Auth\TwoFactorController::class, 'verifyTwoFactor'])->name('2fa.verify');
        Route::post('add', [Auth\TwoFactorController::class, 'create'])->name('2fa.add');
        Route::post('cancel', [Auth\TwoFactorController::class, 'cancelAdd'])->name('2fa.cancel');
        Route::post('remove', [Auth\TwoFactorController::class, 'destroy'])->name('2fa.remove');

        Route::post('{user}/unlock', [Auth\TwoFactorManagementController::class, 'unlock'])->name('2fa.unlock');
        Route::delete('{user}', [Auth\TwoFactorManagementController::class, 'destroy'])->name('2fa.delete');
    });

    // Ajax routes
    Route::prefix('ajax')->group(function () {
        // page ajax controllers
        Route::resource('location', LocationController::class)->only('update', 'destroy');
        Route::resource('pollergroup', PollerGroupController::class)->only('destroy');
        // misc ajax controllers
        Route::get('search/bgp', Ajax\BgpSearchController::class);
        Route::get('search/device', Ajax\DeviceSearchController::class);
        Route::get('search/port', Ajax\PortSearchController::class);
        Route::post('set_map_group', [Ajax\AvailabilityMapController::class, 'setGroup']);
        Route::post('set_map_view', [Ajax\AvailabilityMapController::class, 'setView']);
        Route::post('set_resolution', [Ajax\ResolutionController::class, 'set']);
        Route::get('netcmd', [Ajax\NetCommand::class, 'run']);
        Route::post('ripe/raw', [Ajax\RipeNccApiController::class, 'raw']);
        Route::get('snmp/capabilities', Ajax\SnmpCapabilities::class)->name('snmp.capabilities');

        Route::get('settings/list', [SettingsController::class, 'listAll'])->name('settings.list');

        // js select2 data controllers
        Route::prefix('select')->group(function () {
            Route::get('application', Select\ApplicationController::class)->name('ajax.select.application');
            Route::get('bill', Select\BillController::class)->name('ajax.select.bill');
            Route::get('dashboard', Select\DashboardController::class)->name('ajax.select.dashboard');
            Route::get('device', Select\DeviceController::class)->name('ajax.select.device');
            Route::get('device-field', Select\DeviceFieldController::class)->name('ajax.select.device-field');
            Route::get('device-group', Select\DeviceGroupController::class)->name('ajax.select.device-group');
            Route::get('port-group', Select\PortGroupController::class)->name('ajax.select.port-group');
            Route::get('eventlog', Select\EventlogController::class)->name('ajax.select.eventlog');
            Route::get('graph', Select\GraphController::class)->name('ajax.select.graph');
            Route::get('graph-aggregate', Select\GraphAggregateController::class)->name('ajax.select.graph-aggregate');
            Route::get('graylog-streams', Select\GraylogStreamsController::class)->name('ajax.select.graylog-streams');
            Route::get('syslog', Select\SyslogController::class)->name('ajax.select.syslog');
            Route::get('location', Select\LocationController::class)->name('ajax.select.location');
            Route::get('munin', Select\MuninPluginController::class)->name('ajax.select.munin');
            Route::get('service', Select\ServiceController::class)->name('ajax.select.service');
            Route::get('template', Select\ServiceTemplateController::class)->name('ajax.select.template');
            Route::get('poller-group', Select\PollerGroupController::class)->name('ajax.select.poller-group');
            Route::get('port', Select\PortController::class)->name('ajax.select.port');
            Route::get('port-field', Select\PortFieldController::class)->name('ajax.select.port-field');
        });

        // jquery bootgrid data controllers
        Route::prefix('table')->group(function () {
            Route::post('alert-schedule', Table\AlertScheduleController::class);
            Route::post('customers', Table\CustomersController::class);
            Route::post('device', Table\DeviceController::class);
            Route::post('edit-ports', Table\EditPortsController::class);
            Route::post('eventlog', Table\EventlogController::class);
            Route::post('fdb-tables', Table\FdbTablesController::class);
            Route::post('graylog', Table\GraylogController::class);
            Route::post('location', Table\LocationController::class);
            Route::post('mempools', Table\MempoolsController::class);
            Route::post('outages', Table\OutagesController::class);
            Route::post('port-nac', Table\PortNacController::class);
            Route::post('port-stp', Table\PortStpController::class);
            Route::post('ports', Table\PortsController::class)->name('table.ports');
            Route::post('routes', Table\RoutesTablesController::class);
            Route::post('syslog', Table\SyslogController::class);
            Route::post('vminfo', Table\VminfoController::class);
        });

        // dashboard widgets
        Route::prefix('dash')->group(function () {
            Route::post('alerts', Widgets\AlertsController::class);
            Route::post('alertlog', Widgets\AlertlogController::class);
            Route::post('availability-map', Widgets\AvailabilityMapController::class);
            Route::post('component-status', Widgets\ComponentStatusController::class);
            Route::post('device-summary-horiz', Widgets\DeviceSummaryHorizController::class);
            Route::post('device-summary-vert', Widgets\DeviceSummaryVertController::class);
            Route::post('device-types', Widgets\DeviceTypeController::class);
            Route::post('eventlog', Widgets\EventlogController::class);
            Route::post('generic-graph', Widgets\GraphController::class);
            Route::post('generic-image', Widgets\ImageController::class);
            Route::post('globe', Widgets\GlobeController::class);
            Route::post('graylog', Widgets\GraylogController::class);
            Route::post('placeholder', Widgets\PlaceholderController::class);
            Route::post('notes', Widgets\NotesController::class);
            Route::post('server-stats', Widgets\ServerStatsController::class);
            Route::post('syslog', Widgets\SyslogController::class);
            Route::post('top-devices', Widgets\TopDevicesController::class);
            Route::post('top-interfaces', Widgets\TopInterfacesController::class);
            Route::post('top-errors', Widgets\TopErrorsController::class);
            Route::post('worldmap', Widgets\WorldMapController::class);
            Route::post('alertlog-stats', Widgets\AlertlogStatsController::class);
        });
    });

    // demo helper
    Route::permanentRedirect('demo', '/');
});

// installation routes
Route::prefix('install')->group(function () {
    Route::get('/', [Install\InstallationController::class, 'redirectToFirst'])->name('install');
    Route::get('/checks', [Install\ChecksController::class, 'index'])->name('install.checks');
    Route::get('/database', [Install\DatabaseController::class, 'index'])->name('install.database');
    Route::get('/user', [Install\MakeUserController::class, 'index'])->name('install.user');
    Route::get('/finish', [Install\FinalizeController::class, 'index'])->name('install.finish');

    Route::post('/user/create', [Install\MakeUserController::class, 'create'])->name('install.action.user');
    Route::post('/database/test', [Install\DatabaseController::class, 'test'])->name('install.acton.test-database');
    Route::get('/ajax/database/migrate', [Install\DatabaseController::class, 'migrate'])->name('install.action.migrate');
    Route::get('/ajax/steps', [Install\InstallationController::class, 'stepsCompleted'])->name('install.action.steps');
    Route::any('{path?}', [Install\InstallationController::class, 'invalid'])->where('path', '.*'); // 404
});

// Legacy routes
Route::any('/dummy_legacy_auth/{path?}', [LegacyController::class, 'dummy'])->middleware('auth');
Route::any('/dummy_legacy_unauth/{path?}', [LegacyController::class, 'dummy']);
Route::any('/{path?}', [LegacyController::class, 'index'])
    ->where('path', '^((?!_debugbar).)*')
    ->middleware('auth');
