<?php

use App\Http\Controllers\AboutController;
use App\Http\Controllers\Ajax;
use App\Http\Controllers\AlertController;
use App\Http\Controllers\AlertRuleController;
use App\Http\Controllers\AlertRuleTemplateController;
use App\Http\Controllers\AlertTransportController;
use App\Http\Controllers\Auth;
use App\Http\Controllers\Auth\SocialiteController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DashboardWidgetController;
use App\Http\Controllers\Device;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\DeviceGroupController;
use App\Http\Controllers\GraphController;
use App\Http\Controllers\Install;
use App\Http\Controllers\LegacyController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\Maps;
use App\Http\Controllers\Maps\CustomMapBackgroundController;
use App\Http\Controllers\Maps\CustomMapController;
use App\Http\Controllers\Maps\CustomMapDataController;
use App\Http\Controllers\Maps\CustomMapListController;
use App\Http\Controllers\Maps\CustomMapNodeImageController;
use App\Http\Controllers\Maps\DeviceDependencyController;
use App\Http\Controllers\NacController;
use App\Http\Controllers\OuiLookupController;
use App\Http\Controllers\OutagesController;
use App\Http\Controllers\OverviewController;
use App\Http\Controllers\PluginLegacyController;
use App\Http\Controllers\PluginPageController;
use App\Http\Controllers\PluginSettingsController;
use App\Http\Controllers\PollerController;
use App\Http\Controllers\PollerGroupController;
use App\Http\Controllers\PollerSettingsController;
use App\Http\Controllers\PortController;
use App\Http\Controllers\PortGroupController;
use App\Http\Controllers\PushNotificationController;
use App\Http\Controllers\RealtimeDataController;
use App\Http\Controllers\RealtimeGraphController;
use App\Http\Controllers\Search\PortSecuritySearchController;
use App\Http\Controllers\Select;
use App\Http\Controllers\SensorController;
use App\Http\Controllers\ServiceTemplateController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\Table;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserPreferencesController;
use App\Http\Controllers\ValidateController;
use App\Http\Controllers\Widgets;
use App\Http\Controllers\WidgetSettingsController;
use App\Http\Controllers\WirelessSensorController;
use App\Http\Middleware\AuthenticateGraph;
use Illuminate\Support\Facades\Auth as AuthFacade;
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
AuthFacade::routes(['register' => false, 'reset' => false, 'verify' => false]);

// Socialite
Route::prefix('auth')->name('socialite.')->group(function (): void {
    Route::post('{provider}/redirect', [SocialiteController::class, 'redirect'])->name('redirect');
    Route::match(['get', 'post'], '{provider}/callback', [SocialiteController::class, 'callback'])->name('callback');
    Route::get('{provider}/metadata', [SocialiteController::class, 'metadata'])->name('metadata');
});

Route::get('graph/{path?}', GraphController::class)
    ->where('path', '.*')
    ->middleware(['web', AuthenticateGraph::class])->name('graph');

// WebUI
Route::middleware(['auth'])->group(function (): void {
    // pages
    Route::post('alert/{alert}/ack', [AlertController::class, 'ack'])->name('alert.ack');
    Route::resource('device-groups', DeviceGroupController::class);
    Route::any('inventory', App\Http\Controllers\InventoryController::class)->name('inventory');
    Route::get('inventory/purge', [App\Http\Controllers\InventoryController::class, 'purge'])->name('inventory.purge');
    Route::get('outages', [OutagesController::class, 'index'])->name('outages');
    Route::resource('port', PortController::class)->only('update');
    Route::get('vlans', [App\Http\Controllers\VlansController::class, 'index'])->name('vlans.index');
    Route::prefix('poller')->group(function (): void {
        Route::get('', [PollerController::class, 'pollerTab'])->name('poller.index');
        Route::get('log', [PollerController::class, 'logTab'])->name('poller.log');
        Route::get('groups', [PollerController::class, 'groupsTab'])->name('poller.groups');
        Route::get('settings', [PollerController::class, 'settingsTab'])->name('poller.settings');
        Route::get('performance', [PollerController::class, 'performanceTab'])->name('poller.performance');
        Route::resource('{id}/settings', PollerSettingsController::class, ['as' => 'poller'])->only(['update', 'destroy']);
    });
    Route::prefix('services')->name('services.')->group(function (): void {
        Route::resource('templates', ServiceTemplateController::class);
        Route::post('templates/applyAll', [ServiceTemplateController::class, 'applyAll'])->name('templates.applyAll');
        Route::post('templates/apply/{template}', [ServiceTemplateController::class, 'apply'])->name('templates.apply');
        Route::post('templates/remove/{template}', [ServiceTemplateController::class, 'remove'])->name('templates.remove');
    });
    Route::get('locations', [LocationController::class, 'index']);
    Route::resource('preferences', UserPreferencesController::class)->only('index', 'store');
    Route::resource('users', UserController::class);
    Route::get('about', [AboutController::class, 'index'])->name('about');
    Route::delete('reporting', [AboutController::class, 'clearReportingData'])->name('reporting.clear');
    Route::get('authlog', [UserController::class, 'authlog']);
    Route::get('overview', [OverviewController::class, 'index'])->name('overview');
    Route::get('/', [OverviewController::class, 'index'])->name('home');
    Route::view('vminfo', 'vminfo');

    Route::get('nac', [NacController::class, 'index']);

    // Device Tabs
    Route::middleware('can:admin')->group(function (): void {
        Route::get('/device/{device}/edit', [Device\EditDeviceController::class, 'index'])->name('device.edit');
        Route::put('/device/{device}/edit', [Device\EditDeviceController::class, 'update'])->name('device.edit.update');
        Route::get('/device/{device}/edit/misc', [Device\EditMiscController::class, 'index'])->name('device.edit.misc');
        Route::put('/device/{device}/edit/misc', [Device\EditMiscController::class, 'update'])->name('device.edit.misc.update');
        Route::post('/device/{device}/rediscover', [DeviceController::class, 'rediscover'])->name('device.rediscover');
    });

    Route::prefix('device/{device}')->name('device.')->group(function (): void {
        Route::redirect('logs', 'logs/eventlog')->name('logs');
        Route::get('logs/eventlog', Device\Tabs\EventlogController::class)->name('eventlog');
        Route::get('logs/graylog', Device\Tabs\GraylogController::class)->name('graylog');
        Route::get('logs/outages', Device\Tabs\OutagesController::class)->name('outages');
        Route::get('logs/syslog', Device\Tabs\SyslogController::class)->name('syslog');
        Route::get('popup', App\Http\Controllers\DevicePopupController::class)->name('popup');
        Route::put('notes', [Device\Tabs\NotesController::class, 'update'])->name('notes.update');
        Route::put('module/{module}', [Device\Tabs\ModuleController::class, 'update'])->name('module.update');
        Route::delete('module/{module}', [Device\Tabs\ModuleController::class, 'delete'])->name('module.delete');
    });

    // fallback device routes
    Route::match(['get', 'post'], 'device/{device}/{tab?}/{vars?}', [DeviceController::class, 'index'])
        ->name('device')->where('vars', '.*');

    // Maps
    Route::get('fullscreenmap', [Maps\FullscreenMapController::class, 'fullscreenMap']);
    Route::get('availability-map', [Maps\AvailabilityMapController::class, 'availabilityMap']);
    Route::get('map/{vars?}', [Maps\NetMapController::class, 'netMap']);
    Route::prefix('maps')->group(function (): void {
        Route::resource('custom', CustomMapController::class, ['as' => 'maps'])
            ->parameters(['custom' => 'map'])->except('create');
        Route::post('custom/{map}/clone', [CustomMapController::class, 'clone'])->name('maps.custom.clone');
        Route::get('custom/{map}/background', [CustomMapBackgroundController::class, 'get'])->name('maps.custom.background');
        Route::post('custom/{map}/background', [CustomMapBackgroundController::class, 'save'])->name('maps.custom.background.save');
        Route::get('custom/{map}/data', [CustomMapDataController::class, 'get'])->name('maps.custom.data');
        Route::post('custom/{map}/data', [CustomMapDataController::class, 'save'])->name('maps.custom.data.save');
        Route::get('customlist', [CustomMapListController::class, 'index'])->name('maps.custom.list');
        Route::get('devicedependency', [DeviceDependencyController::class, 'dependencyMap']);
        Route::post('getdevices', [Maps\MapDataController::class, 'getDevices'])->name('maps.getdevices');
        Route::post('getdevicelinks', [Maps\MapDataController::class, 'getDeviceLinks'])->name('maps.getdevicelinks');
        Route::post('getgeolinks', [Maps\MapDataController::class, 'getGeographicLinks'])->name('maps.getgeolinks');
        Route::post('getservices', [Maps\MapDataController::class, 'getServices'])->name('maps.getservices');
        Route::get('nodeimage', [CustomMapNodeImageController::class, 'index'])->name('maps.nodeimage.index');
        Route::post('nodeimage', [CustomMapNodeImageController::class, 'store'])->name('maps.nodeimage.store');
        Route::delete('nodeimage/{image}', [CustomMapNodeImageController::class, 'destroy'])->name('maps.nodeimage.destroy');
        Route::get('nodeimage/{image}', [CustomMapNodeImageController::class, 'show'])->name('maps.nodeimage.show');
        Route::post('nodeimage/{image}', [CustomMapNodeImageController::class, 'update'])->name('maps.nodeimage.update');
    });
    Route::get('maps/devicedependency', [DeviceDependencyController::class, 'dependencyMap']);

    // dashboard
    Route::resource('dashboard', DashboardController::class)->except(['create', 'edit']);
    Route::post('dashboard/{dashboard}/copy', [DashboardController::class, 'copy'])->name('dashboard.copy');
    Route::post('dashboard/{dashboard}/widgets', [DashboardWidgetController::class, 'add'])->name('dashboard.widget.add');
    Route::delete('dashboard/{dashboard}/widgets', [DashboardWidgetController::class, 'clear'])->name('dashboard.widget.clear');
    Route::put('dashboard/{dashboard}/widgets', [DashboardWidgetController::class, 'update'])->name('dashboard.widget.update');
    Route::delete('dashboard/widgets/{widget}', [DashboardWidgetController::class, 'remove'])->name('dashboard.widget.remove');
    Route::put('dashboard/widgets/{widget}', [WidgetSettingsController::class, 'update'])->name('dashboard.widget.settings');

    Route::get('tool/oui-lookup', OuiLookupController::class)->name('tool.oui-lookup');

    // Push notifications
    Route::prefix('push')->group(function (): void {
        Route::get('token', [PushNotificationController::class, 'token'])->name('push.token');
        Route::get('key', [PushNotificationController::class, 'key'])->name('push.key');
        Route::post('register', [PushNotificationController::class, 'register'])->name('push.register');
        Route::post('unregister', [PushNotificationController::class, 'unregister'])->name('push.unregister');
    });

    // admin pages
    Route::middleware('can:admin')->group(function (): void {
        Route::get('settings/{tab?}/{section?}', [SettingsController::class, 'index'])->name('settings');
        Route::put('settings/{name}', [SettingsController::class, 'update'])->name('settings.update');
        Route::delete('settings/{name}', [SettingsController::class, 'destroy'])->name('settings.destroy');

        Route::post('alert/transports/{transport}/test', [AlertTransportController::class, 'test'])->name('alert.transports.test');
        Route::resource('alert-rule', AlertRuleController::class)->only(['show', 'store', 'update', 'destroy']);
        Route::put('alert-rule/{alert_rule}/toggle', [AlertRuleController::class, 'toggle'])->name('alert-rule.toggle');
        Route::get('alert-rule-from-template/{template_id}', [AlertRuleTemplateController::class, 'template'])->name('alert-rule-template');
        Route::get('alert-rule-from-rule/{alert_rule}', [AlertRuleTemplateController::class, 'rule'])->name('alert-rule-template.rule');
        Route::get('alertlog/{alertLog}/details', Ajax\AlertDetailsController::class)->name('alertlog.details');

        Route::get('plugin/settings', App\Http\Controllers\PluginAdminController::class)->name('plugin.admin');
        Route::get('plugin/settings/{plugin:plugin_name}', PluginSettingsController::class)->name('plugin.settings');
        Route::post('plugin/settings/{plugin:plugin_name}', [PluginSettingsController::class, 'update'])->name('plugin.update');

        Route::resource('port-groups', PortGroupController::class);
        Route::get('validate', [ValidateController::class, 'index'])->name('validate');
        Route::get('validate/results/{group?}', [ValidateController::class, 'runValidation'])->name('validate.results');
        Route::post('validate/fix', [ValidateController::class, 'runFixer'])->name('validate.fix');
    });

    Route::get('plugin', [PluginLegacyController::class, 'redirect']);
    Route::redirect('plugin/view=admin', '/plugin/admin');
    Route::get('plugin/p={pluginName}', [PluginLegacyController::class, 'redirect']);
    Route::any('plugin/v1/{plugin:plugin_name}/{other?}', PluginLegacyController::class)->where('other', '(.*)')->name('plugin.legacy');
    Route::get('plugin/{plugin:plugin_name}', PluginPageController::class)->name('plugin.page');

    // Search pages
    Route::get('search/secureports', [PortSecuritySearchController::class, 'index'])->name('search.secureports');

    Route::get('health/{metric?}/{legacyview?}', [SensorController::class, 'index'])->name('sensor.index');
    Route::get('wireless/{metric}/{legacyview?}', [WirelessSensorController::class, 'index'])->name('wireless.index');

    // old route redirects
    Route::permanentRedirect('poll-log', 'poller/log');

    // Two Factor Auth
    Route::prefix('2fa')->group(function (): void {
        Route::get('', [Auth\TwoFactorController::class, 'showTwoFactorForm'])->name('2fa.form');
        Route::post('', [Auth\TwoFactorController::class, 'verifyTwoFactor'])->name('2fa.verify');
        Route::post('add', [Auth\TwoFactorController::class, 'create'])->name('2fa.add');
        Route::post('cancel', [Auth\TwoFactorController::class, 'cancelAdd'])->name('2fa.cancel');
        Route::post('remove', [Auth\TwoFactorController::class, 'destroy'])->name('2fa.remove');

        Route::post('{user}/unlock', [Auth\TwoFactorManagementController::class, 'unlock'])->name('2fa.unlock');
        Route::delete('{user}', [Auth\TwoFactorManagementController::class, 'destroy'])->name('2fa.delete');
    });

    Route::get('realtime/graph/{port}', RealtimeGraphController::class)->name('realtime.graph');
    Route::get('realtime/data/{port}', RealtimeDataController::class)->name('realtime.data');

    // Ajax routes
    Route::prefix('ajax')->group(function (): void {
        // page ajax controllers
        Route::resource('location', LocationController::class)->only('update', 'destroy');
        Route::resource('pollergroup', PollerGroupController::class)->only('destroy');
        // misc ajax controllers
        Route::get('search/bgp', Ajax\BgpSearchController::class);
        Route::get('search/device', Ajax\DeviceSearchController::class);
        Route::get('search/port', Ajax\PortSearchController::class);
        Route::post('set_map_group', [Ajax\AvailabilityMapController::class, 'setGroup']);
        Route::post('set_map_view', [Ajax\AvailabilityMapController::class, 'setView']);
        Route::post('set_resolution', [Ajax\SessionController::class, 'resolution']);
        Route::post('set_style', [Ajax\SessionController::class, 'style']);
        Route::post('ripe/raw', [Ajax\RipeNccApiController::class, 'raw']);
        Route::get('snmp/capabilities', Ajax\SnmpCapabilities::class)->name('snmp.capabilities');

        Route::get('settings/list', [SettingsController::class, 'listAll'])->name('settings.list');

        // js select2 data controllers
        Route::prefix('select')->group(function (): void {
            Route::get('alert-transport', Select\AlertTransportController::class)->name('ajax.select.alert-transport');
            Route::get('alert-transport-group', Select\AlertTransportGroupController::class)->name('ajax.select.alert-transport-group');
            Route::get('alert-transports-groups', Select\AlertTransportsAndGroupsController::class)->name('ajax.select.alert-transports-groups');
            Route::get('application', Select\ApplicationController::class)->name('ajax.select.application');
            Route::get('bill', Select\BillController::class)->name('ajax.select.bill');
            Route::get('custom-map', Select\CustomMapController::class)->name('ajax.select.custom-map');
            Route::get('custom-map-menu-group', Select\CustomMapMenuGroupController::class)->name('ajax.select.custom-map-menu-group');
            Route::get('dashboard', Select\DashboardController::class)->name('ajax.select.dashboard');
            Route::get('device', Select\DeviceController::class)->name('ajax.select.device');
            Route::get('devices-groups-locations', Select\DevicesGroupsAndLocationsController::class)->name('ajax.select.devices-groups-locations');
            Route::get('device-field', Select\DeviceFieldController::class)->name('ajax.select.device-field');
            Route::get('device-group', Select\DeviceGroupController::class)->name('ajax.select.device-group');
            Route::get('port-group', Select\PortGroupController::class)->name('ajax.select.port-group');
            Route::get('eventlog', Select\EventlogController::class)->name('ajax.select.eventlog');
            Route::get('graph', Select\GraphController::class)->name('ajax.select.graph');
            Route::get('graph-aggregate', Select\GraphAggregateController::class)->name('ajax.select.graph-aggregate');
            Route::get('graylog-streams', Select\GraylogStreamsController::class)->name('ajax.select.graylog-streams');
            Route::get('inventory', Select\InventoryController::class)->name('ajax.select.inventory');
            Route::get('syslog', Select\SyslogController::class)->name('ajax.select.syslog');
            Route::get('location', Select\LocationController::class)->name('ajax.select.location');
            Route::get('munin', Select\MuninPluginController::class)->name('ajax.select.munin');
            Route::get('os', Select\OsController::class)->name('ajax.select.os');
            Route::get('role', Select\RoleController::class)->name('ajax.select.role');
            Route::get('service', Select\ServiceController::class)->name('ajax.select.service');
            Route::get('customoid', Select\CustomoidController::class)->name('ajax.select.customoid');
            Route::get('template', Select\ServiceTemplateController::class)->name('ajax.select.template');
            Route::get('poller-group', Select\PollerGroupController::class)->name('ajax.select.poller-group');
            Route::get('port', Select\PortController::class)->name('ajax.select.port');
            Route::get('port-field', Select\PortFieldController::class)->name('ajax.select.port-field');
            Route::get('sensor', Select\SensorController::class)->name('ajax.select.sensor');
        });

        // jquery bootgrid data controllers
        Route::prefix('table')->group(function (): void {
            Route::post('address-search/ipv4', Table\Ipv4AddressSearchController::class)->name('search.ipv4');
            Route::post('address-search/ipv6', Table\Ipv6AddressSearchController::class)->name('search.ipv6');
            Route::post('address-search/mac', Table\MacSearchController::class)->name('search.mac');
            Route::post('alertlog', Table\AlertLogController::class)->name('table.alertlog');
            Route::get('alertlog/export', [Table\AlertLogController::class, 'export'])->name('table.alertlog.export');
            Route::post('alert-schedule', Table\AlertScheduleController::class);
            Route::post('customers', Table\CustomersController::class);
            Route::post('diskio', Table\DiskioController::class)->name('table.diskio');
            Route::post('device', Table\DeviceController::class)->name('table.device');
            Route::get('device/export', [Table\DeviceController::class, 'export']);
            Route::post('edit-ports', Table\EditPortsController::class);
            Route::post('eventlog', Table\EventlogController::class)->name('table.eventlog');
            Route::post('fdb-tables', Table\FdbTablesController::class);
            Route::post('graylog', Table\GraylogController::class)->name('table.graylog');
            Route::post('inventory', Table\InventoryController::class)->name('table.inventory');
            Route::get('inventory/export', [Table\InventoryController::class, 'export']);
            Route::post('location', Table\LocationController::class)->name('table.location');
            Route::post('mempools', Table\MempoolsController::class)->name('table.mempools');
            Route::get('mempools/export', [Table\MempoolsController::class, 'export']);
            Route::post('outages', Table\OutagesController::class)->name('table.outages');
            Route::get('outages/export', [Table\OutagesController::class, 'export']);
            Route::post('port-nac', Table\PortNacController::class)->name('table.port-nac');
            Route::post('port-security', Table\PortSecurityController::class)->name('table.port-security');
            Route::post('port-stp', Table\PortStpController::class);
            Route::post('ports', Table\PortsController::class)->name('table.ports');
            Route::get('ports/export', [Table\PortsController::class, 'export']);
            Route::post('processors', Table\ProcessorsController::class)->name('table.processors');
            Route::get('processors/export', [Table\ProcessorsController::class, 'export']);
            Route::post('routes', Table\RoutesTablesController::class);
            Route::post('sensors', Table\SensorsController::class)->name('table.sensors');
            Route::get('sensors/export', [Table\SensorsController::class, 'export']);
            Route::post('storages', Table\StoragesController::class)->name('table.storages');
            Route::get('storages/export', [Table\StoragesController::class, 'export']);
            Route::post('syslog', Table\SyslogController::class)->name('table.syslog');
            Route::post('printer-supply', Table\PrinterSupplyController::class)->name('table.printer-supply');
            Route::post('tnmsne', Table\TnmsneController::class)->name('table.tnmsne');
            Route::post('wireless', Table\WirelessSensorController::class)->name('table.wireless');
            Route::post('vlan-ports', Table\VlanPortsController::class)->name('table.vlan-ports');
            Route::post('vlan-devices', Table\VlanDevicesController::class)->name('table.vlan-devices');
            Route::post('vminfo', Table\VminfoController::class);
        });

        // dashboard widgets
        Route::prefix('dash')->group(function (): void {
            Route::post('alerts', Widgets\AlertsController::class);
            Route::post('alertlog', Widgets\AlertlogController::class);
            Route::post('alert-map', Widgets\AlertMapController::class);
            Route::post('alertlog-stats', Widgets\AlertlogStatsController::class);
            Route::post('availability-map', Widgets\AvailabilityMapController::class);
            Route::post('component-status', Widgets\ComponentStatusController::class);
            Route::post('custom-map', Widgets\CustomMapController::class);
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
            Route::post('worldmap', Widgets\WorldMapController::class)->name('widget.worldmap');
        });
    });

    // demo helper
    Route::permanentRedirect('demo', '/');
});

// routes that don't need authentication
Route::prefix('ajax')->group(function (): void {
    Route::post('set_timezone', [Ajax\TimezoneController::class, 'set']);
});

// installation routes
Route::prefix('install')->group(function (): void {
    Route::get('/', [Install\InstallationController::class, 'redirectToFirst'])->name('install');
    Route::get('/checks', [Install\ChecksController::class, 'index'])->name('install.checks');
    Route::get('/database', [Install\DatabaseController::class, 'index'])->name('install.database');
    Route::get('/user', [Install\MakeUserController::class, 'index'])->name('install.user');
    Route::get('/finish', [Install\FinalizeController::class, 'index'])->name('install.finish');

    Route::post('/finish', [Install\FinalizeController::class, 'saveConfig'])->name('install.finish.save');
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
