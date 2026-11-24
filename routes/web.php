<?php

use App\Http\Controllers\AboutController;
use App\Http\Controllers\Ajax;
use App\Http\Controllers\AlertController;
use App\Http\Controllers\AlertHistoryController;
use App\Http\Controllers\AlertOperationController;
use App\Http\Controllers\AlertRuleController;
use App\Http\Controllers\AlertRuleTemplateController;
use App\Http\Controllers\AlertScheduleController;
use App\Http\Controllers\AlertStatsController;
use App\Http\Controllers\AlertTemplateController;
use App\Http\Controllers\AlertTransportController;
use App\Http\Controllers\AlertTransportGroupController;
use App\Http\Controllers\ApiAccessController;
use App\Http\Controllers\AppsController;
use App\Http\Controllers\Auth;
use App\Http\Controllers\Auth\SocialiteController;
use App\Http\Controllers\AuthLogController;
use App\Http\Controllers\BillController;
use App\Http\Controllers\CustomoidController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DashboardWidgetController;
use App\Http\Controllers\Device;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\DeviceDependencyController;
use App\Http\Controllers\DeviceGroupController;
use App\Http\Controllers\DevicesController;
use App\Http\Controllers\FaultsController;
use App\Http\Controllers\FirstRunController;
use App\Http\Controllers\GraphController;
use App\Http\Controllers\GraphDataController;
use App\Http\Controllers\GraphsPageController;
use App\Http\Controllers\Install;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\LogController;
use App\Http\Controllers\Maps;
use App\Http\Controllers\Maps\CustomMapBackgroundController;
use App\Http\Controllers\Maps\CustomMapController;
use App\Http\Controllers\Maps\CustomMapDataController;
use App\Http\Controllers\Maps\CustomMapListController;
use App\Http\Controllers\Maps\CustomMapNodeImageController;
use App\Http\Controllers\NacController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OuiLookupController;
use App\Http\Controllers\OutagesController;
use App\Http\Controllers\OverviewController;
use App\Http\Controllers\OxidizedController;
use App\Http\Controllers\PackagesController;
use App\Http\Controllers\PeeringController;
use App\Http\Controllers\PluginLegacyController;
use App\Http\Controllers\PluginPageController;
use App\Http\Controllers\PluginSettingsController;
use App\Http\Controllers\PollerController;
use App\Http\Controllers\PollerGroupController;
use App\Http\Controllers\PollerSettingsController;
use App\Http\Controllers\PortController;
use App\Http\Controllers\PortGroupController;
use App\Http\Controllers\PortGroupPortsController;
use App\Http\Controllers\PortSecurityController;
use App\Http\Controllers\PortTypeController;
use App\Http\Controllers\PseudowiresController;
use App\Http\Controllers\PushNotificationController;
use App\Http\Controllers\QueueController;
use App\Http\Controllers\RealtimeDataController;
use App\Http\Controllers\RealtimeGraphController;
use App\Http\Controllers\RipeNccApiController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\Routing;
use App\Http\Controllers\Search;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SecretController;
use App\Http\Controllers\Select;
use App\Http\Controllers\SensorController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\ServiceTemplateController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\SslCertificateController;
use App\Http\Controllers\Table;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserPermissionsController;
use App\Http\Controllers\UserPreferencesController;
use App\Http\Controllers\ValidateController;
use App\Http\Controllers\VminfoController;
use App\Http\Controllers\Widgets;
use App\Http\Controllers\WidgetSettingsController;
use App\Http\Controllers\WirelessSensorController;
use App\Http\Controllers\ZiggyRoutesController;
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
AuthFacade::routes(['register' => false, 'reset' => false, 'verify' => false, 'confirm' => false]);

// Socialite
Route::prefix('auth')->name('socialite.')->group(function (): void {
    Route::post('{provider}/redirect', [SocialiteController::class, 'redirect'])->name('redirect');
    Route::match(['get', 'post'], '{provider}/callback', [SocialiteController::class, 'callback'])->name('callback');
    Route::get('{provider}/metadata', [SocialiteController::class, 'metadata'])->name('metadata');
});

Route::get('graph/{path?}', GraphController::class)
    ->where('path', '.*')
    ->middleware(['web', AuthenticateGraph::class])->name('graph');

Route::get('js/routes', ZiggyRoutesController::class)
    ->middleware(['auth', 'cache.headers:private;max_age=0;must_revalidate;etag'])->name('js.routes');

// WebUI
Route::middleware(['auth'])->group(function (): void {
    // pages
    Route::post('alert/{fault}/ack', [AlertController::class, 'ack'])->name('alert.ack');
    Route::post('fault/{fault}/ack', [AlertController::class, 'ack'])->name('fault.ack');
    Route::get('fault/{fault}/notes', [AlertController::class, 'showNote'])->whereNumber('fault')->name('fault.notes.show');
    Route::patch('fault/{fault}/notes', [AlertController::class, 'updateNote'])->whereNumber('fault')->name('fault.notes.update');
    Route::get('devices/{view?}/{graph?}/{vars?}', [DevicesController::class, 'index'])->where('vars', '.*')
        ->middleware(['saved-filter:devices'])
        ->name('devices');
    Route::get('alert-log/{vars?}', [AlertHistoryController::class, 'index'])->where('vars', '.*')
        ->middleware('saved-filter:alert-log')->name('alert-log');
    Route::get('alert-stats', AlertStatsController::class)->name('alert-stats');
    Route::get('alert-schedule', [AlertScheduleController::class, 'index'])->name('alert-schedule.index');
    Route::get('faults/{vars?}', [FaultsController::class, 'index'])->where('vars', '.*')
        ->middleware('saved-filter:faults')->name('faults');
    Route::get('alerts/{vars?}', [FaultsController::class, 'index'])->where('vars', '.*')
        ->middleware('saved-filter:faults'); // legacy alias
    Route::view('customers', 'customers.index')->name('customers');
    Route::resource('device-groups', DeviceGroupController::class);
    Route::get('device-dependencies', [DeviceDependencyController::class, 'index'])->name('device-dependencies.index');
    Route::post('device-groups/{device_group}/rediscover', [DeviceGroupController::class, 'rediscover'])->name('device-groups.rediscover');
    Route::get('graph-data', GraphDataController::class)->name('graph.data');
    Route::get('graphs/{path?}', GraphsPageController::class)->where('path', '.*')->name('graphs');
    Route::any('inventory', App\Http\Controllers\InventoryController::class)->middleware('saved-filter:inventory')->name('inventory');
    Route::get('inventory/purge', [App\Http\Controllers\InventoryController::class, 'purge'])->name('inventory.purge');
    Route::get('outages', [OutagesController::class, 'index'])->name('outages');
    Route::get('oxidized', [OxidizedController::class, 'index'])->name('oxidized');
    Route::post('oxidized/search', [OxidizedController::class, 'search'])->name('oxidized.search');
    Route::post('oxidized/reload', [OxidizedController::class, 'reload'])->name('oxidized.reload');
    Route::get('eventlog', [LogController::class, 'eventlog'])->middleware('saved-filter:eventlog')->name('eventlog');
    Route::get('syslog', [LogController::class, 'syslog'])->middleware('saved-filter:syslog')->name('syslog');
    Route::get('syslog/action=expunge', [LogController::class, 'confirmSyslogExpunge']); // legacy bookmark
    Route::post('syslog/expunge', [LogController::class, 'expungeSyslog'])->name('syslog.expunge');
    Route::get('graylog', [LogController::class, 'graylog'])->name('graylog');
    Route::get('packages', [PackagesController::class, 'index'])->middleware('saved-filter:packages')->name('packages.index');
    Route::get('packages/{legacy}', [PackagesController::class, 'legacy'])->where('legacy', '.*=.*');
    Route::prefix('peering')->name('peering.')->group(function (): void {
        Route::get('', [PeeringController::class, 'index'])->name('index');
        Route::get('{asn}', [PeeringController::class, 'exchanges'])->name('exchanges')->whereNumber('asn');
        Route::get('{asn}/{ix}', [PeeringController::class, 'peers'])->name('peers')->whereNumber(['asn', 'ix']);
    });
    Route::resource('port', PortController::class)->only('update');
    Route::put('port/{port}/notes', [PortController::class, 'notes'])->name('port.notes.update');
    Route::get('port/{port}/popup', App\Http\Controllers\PortPopupController::class)->name('port.popup');
    Route::get('vlans', [App\Http\Controllers\VlansController::class, 'index'])->name('vlans.index');
    Route::prefix('routing')->name('routing.')->middleware('can:viewAny,App\Models\Route')->group(function (): void {
        Route::get('bgp', Routing\BgpController::class)->name('bgp');
        Route::get('cef', Routing\CefController::class)->name('cef');
        Route::get('cisco-otv', Routing\CiscoOtvController::class)->name('cisco-otv');
        Route::get('isis', Routing\IsisController::class)->name('isis');
        Route::get('mpls', Routing\MplsController::class)->name('mpls');
        Route::get('ospf', Routing\OspfController::class)->name('ospf');
        Route::get('ospfv3', Routing\Ospfv3Controller::class)->name('ospfv3');
        Route::get('vrf', Routing\VrfController::class)->name('vrf');
        Route::get('{legacy?}', Routing\RoutingController::class)->where('legacy', '.*=.*')->name('index');
    });
    Route::get('pseudowires', PseudowiresController::class)->name('pseudowires');
    Route::get('port-security', [PortSecurityController::class, 'index'])->name('port-security.index');
    Route::prefix('search')->group(function (): void {
        Route::get('', [SearchController::class, 'index'])->middleware('saved-filter:network-search')->name('search');
        Route::get('results', Search\HostResultsController::class)->name('search.results');
        Route::get('name', Search\HostNameController::class)->middleware('signed')->name('search.name');
        Route::get('export', Search\HostExportController::class)->name('search.export');
        Route::match(['get', 'post'], '{legacy}', [SearchController::class, 'legacy'])->where('legacy', '.*=.*');
    });
    Route::get('porttype/{type}', [PortTypeController::class, 'graph'])->name('porttype.graph');
    Route::get('portgroup/{group}', [PortGroupController::class, 'graph'])->name('portgroup.graph')->whereNumber('group');
    Route::prefix('poller')->group(function (): void {
        Route::get('', [PollerController::class, 'pollerTab'])->name('poller.index');
        Route::get('log', [PollerController::class, 'logTab'])->name('poller.log');
        Route::get('groups', [PollerController::class, 'groupsTab'])->name('poller.groups');
        Route::get('settings', [PollerController::class, 'settingsTab'])->name('poller.settings');
        Route::get('performance', [PollerController::class, 'performanceTab'])->name('poller.performance');
        Route::get('queues', [QueueController::class, 'index'])->name('poller.queues');
        Route::post('queues/failed/{id}/retry', [QueueController::class, 'retry'])->name('poller.queues.retry');
        Route::delete('queues/failed/{id}', [QueueController::class, 'forget'])->name('poller.queues.forget');
        Route::delete('queues/locks/{device}', [QueueController::class, 'releaseLock'])->name('poller.queues.release-lock');
        Route::resource('{id}/settings', PollerSettingsController::class, ['as' => 'poller'])->only(['update', 'destroy']);
        Route::delete('{poller}', [PollerController::class, 'destroy'])->name('poller.destroy');
        Route::delete('cluster/{poller_cluster}', [PollerController::class, 'destroyCluster'])->name('poller-cluster.destroy');
    });
    Route::delete('ports/purge', [App\Http\Controllers\PortsController::class, 'purge'])->name('ports.purge');
    Route::get('ports/{view?}/{graph?}', [App\Http\Controllers\PortsController::class, 'index'])
        ->middleware('saved-filter:ports')->name('ports');
    Route::prefix('services')->name('services.')->group(function (): void {
        Route::resource('templates', ServiceTemplateController::class);
        Route::post('templates/applyAll', [ServiceTemplateController::class, 'applyAll'])->name('templates.applyAll');
        Route::post('templates/apply/{template}', [ServiceTemplateController::class, 'apply'])->name('templates.apply');
        Route::post('templates/remove/{template}', [ServiceTemplateController::class, 'remove'])->name('templates.remove');
    });
    Route::resource('services', ServiceController::class)->except(['create'])->where(['service' => '[0-9]+']);
    Route::get('services/{legacy}', [ServiceController::class, 'legacy'])->where('legacy', '[^/=]+=.*');
    Route::redirect('addsrv', 'services#create-service');
    Route::post('customoid/test/{customoid?}', [CustomoidController::class, 'test'])->name('customoid.test');
    Route::resource('customoid', CustomoidController::class)->only(['show', 'store', 'update', 'destroy']);
    Route::get('bills', [BillController::class, 'index'])->name('bills.index');
    Route::post('bills', [BillController::class, 'store'])->name('bill.store');
    Route::get('bill/bill_id={bill}/{vars?}', [BillController::class, 'legacyRedirect'])->where('vars', '.*');
    Route::resource('bill', BillController::class)->only(['show', 'edit', 'update', 'destroy'])->whereNumber('bill');
    Route::prefix('bill/{bill}')->name('bill.')->whereNumber('bill')->group(function (): void {
        Route::get('accurate', [BillController::class, 'accurate'])->name('accurate');
        Route::get('transfer', [BillController::class, 'transfer'])->name('transfer');
        Route::get('history', [BillController::class, 'history'])->name('history');
        Route::post('reset', [BillController::class, 'reset'])->name('reset');
        Route::post('ports', [BillController::class, 'attachPort'])->name('port.attach');
        Route::delete('ports/{port}', [BillController::class, 'detachPort'])->name('port.detach');
    });
    Route::get('locations', [LocationController::class, 'index'])->name('locations.index');
    Route::resource('ssl-certificates', SslCertificateController::class)->except(['edit']);
    Route::resource('preferences', UserPreferencesController::class)->only('index', 'store', 'update');
    Route::middleware('can:api.access')->group(function (): void {
        Route::get('api-access', [ApiAccessController::class, 'index'])->name('api-access.index');
        Route::post('api-access', [ApiAccessController::class, 'store'])->name('api-access.store');
        Route::patch('api-access/{id}', [ApiAccessController::class, 'update'])->name('api-access.update')->whereNumber('id');
        Route::post('api-access/{id}/reset', [ApiAccessController::class, 'reset'])->name('api-access.reset')->whereNumber('id');
        Route::delete('api-access/{id}', [ApiAccessController::class, 'destroy'])->name('api-access.destroy')->whereNumber('id');
    });
    Route::resource('users', UserController::class);
    Route::prefix('users/{user}/permissions')->name('users.permissions.')->group(function (): void {
        Route::get('', [UserPermissionsController::class, 'edit'])->name('edit');
        Route::post('device', [UserPermissionsController::class, 'attachDevice'])->name('device.attach');
        Route::delete('device/{device}', [UserPermissionsController::class, 'detachDevice'])->name('device.detach')->whereNumber('device');
        Route::post('device-group', [UserPermissionsController::class, 'attachDeviceGroup'])->name('device-group.attach');
        Route::delete('device-group/{deviceGroup}', [UserPermissionsController::class, 'detachDeviceGroup'])->name('device-group.detach')->whereNumber('deviceGroup');
        Route::post('port', [UserPermissionsController::class, 'attachPort'])->name('port.attach');
        Route::delete('port/{port}', [UserPermissionsController::class, 'detachPort'])->name('port.detach')->whereNumber('port');
        Route::post('bill', [UserPermissionsController::class, 'attachBill'])->name('bill.attach');
        Route::delete('bill/{bill}', [UserPermissionsController::class, 'detachBill'])->name('bill.detach')->whereNumber('bill');
    });
    Route::resource('secrets', SecretController::class);
    Route::prefix('first-run')->name('first-run')->group(function (): void {
        Route::get('/', [FirstRunController::class, 'index']);
        Route::post('credentials', [FirstRunController::class, 'credentials'])->name('.credentials');
        Route::post('networks', [FirstRunController::class, 'networks'])->name('.networks');
        Route::post('scan', [FirstRunController::class, 'scan'])->name('.scan');
        Route::post('finish', [FirstRunController::class, 'finish'])->name('.finish');
    });
    Route::get('about', [AboutController::class, 'index'])->name('about');
    Route::delete('reporting', [AboutController::class, 'clearReportingData'])->name('reporting.clear');
    Route::get('authlog', [AuthLogController::class, 'index'])->name('auth-log');
    Route::get('overview', [OverviewController::class, 'index'])->name('overview');
    Route::get('/', [OverviewController::class, 'index'])->name('home');
    Route::get('vminfo', [VminfoController::class, 'index'])->name('vminfo.index');
    Route::get('apps', [AppsController::class, 'index'])->name('apps.index');
    Route::get('apps/{vars}', [AppsController::class, 'legacy'])->where('vars', '.+');

    Route::get('nac', [NacController::class, 'index'])->name('nac');

    // Device Tabs
    Route::get('/device/add', [Device\AddDeviceController::class, 'index'])->name('device.add');
    Route::post('/device/add', [Device\AddDeviceController::class, 'store'])->name('device.add.store');
    Route::redirect('/addhost', '/device/add');
    Route::redirect('/api-docs', 'https://docs.librenms.org/API/');
    Route::get('/device/{device}/edit', [Device\EditDeviceController::class, 'index'])->name('device.edit');
    Route::put('/device/{device}/edit', [Device\EditDeviceController::class, 'update'])->name('device.edit.update');
    Route::get('/device/{device}/edit/apps', [Device\EditApplicationsController::class, 'index'])->name('device.edit.apps');
    Route::put('/device/{device}/edit/apps/{type}', [Device\EditApplicationsController::class, 'update'])->name('device.edit.apps.update');
    Route::get('/device/{device}/edit/components', [Device\EditComponentsController::class, 'index'])->name('device.edit.components');
    Route::put('/device/{device}/edit/components', [Device\EditComponentsController::class, 'update'])->name('device.edit.components.update');
    Route::get('/device/{device}/edit/customoids', [Device\EditCustomoidsController::class, 'index'])->name('device.edit.customoids');
    Route::get('/device/{device}/edit/alert-rules', [AlertRuleController::class, 'device'])->name('device.edit.alert-rules');
    Route::get('/device/{device}/edit/health', [Device\EditHealthController::class, 'index'])->name('device.edit.health');
    Route::post('/device/{device}/edit/health/reset', [Device\EditHealthController::class, 'reset'])->name('device.edit.health.reset');
    Route::post('/device/{device}/edit/health/{sensor}', [Device\EditHealthController::class, 'update'])->name('device.edit.health.update')->scopeBindings()->whereNumber('sensor');
    Route::get('/device/{device}/edit/mempools', [Device\EditMempoolsController::class, 'index'])->name('device.edit.mempools');
    Route::post('/device/{device}/edit/mempools/{mempool}', [Device\EditMempoolsController::class, 'update'])->name('device.edit.mempools.update')->scopeBindings();
    Route::get('/device/{device}/edit/misc', [Device\EditMiscController::class, 'index'])->name('device.edit.misc');
    Route::put('/device/{device}/edit/misc', [Device\EditMiscController::class, 'update'])->name('device.edit.misc.update');
    Route::get('/device/{device}/edit/modules', [Device\EditModulesController::class, 'index'])->name('device.edit.modules');
    Route::put('/device/{device}/edit/modules/{module}', [Device\EditModulesController::class, 'update'])->name('device.edit.modules.update');
    Route::delete('/device/{device}/edit/modules/{module}', [Device\EditModulesController::class, 'delete'])->name('device.edit.modules.delete');
    Route::get('/device/{device}/edit/polling', [Device\EditPollingController::class, 'index'])->name('device.edit.polling');
    Route::post('/device/{device}/edit/polling', [Device\EditPollingController::class, 'store'])->name('device.edit.polling.store');
    Route::put('/device/{device}/edit/polling/{methodType}', [Device\EditPollingController::class, 'update'])->name('device.edit.polling.update');
    Route::delete('/device/{device}/edit/polling/{methodType}', [Device\EditPollingController::class, 'destroy'])->name('device.edit.polling.destroy')
        ->where('methodType', '.+'); // stored unknown types may contain slashes
    Route::get('/device/{device}/edit/ports', [Device\EditPortsController::class, 'index'])->name('device.edit.ports')->middleware('saved-filter:device.edit-ports');
    Route::get('/device/{device}/edit/ports/list', [Device\EditPortsController::class, 'ports'])->name('device.edit.ports.list');
    Route::put('/device/{device}/edit/ports/settings', [Device\EditPortsController::class, 'settings'])->name('device.edit.ports.settings');
    Route::post('/device/{device}/edit/ports/bulk', [Device\EditPortsController::class, 'bulk'])->name('device.edit.ports.bulk');
    Route::post('/device/{device}/edit/ports/reset-state', [Device\EditPortsController::class, 'resetState'])->name('device.edit.ports.reset-state');
    Route::patch('/device/{device}/edit/ports/{port}', [Device\EditPortsController::class, 'update'])->name('device.edit.ports.update')->scopeBindings()->whereNumber('port');
    Route::get('/device/{device}/edit/processors', [Device\EditProcessorsController::class, 'index'])->name('device.edit.processors');
    Route::post('/device/{device}/edit/processors/{processor}', [Device\EditProcessorsController::class, 'update'])->name('device.edit.processors.update')->scopeBindings();
    Route::get('/device/{device}/edit/routing', [Device\EditRoutingController::class, 'index'])->name('device.edit.routing');
    Route::post('/device/{device}/edit/routing/vrf', [Device\EditRoutingController::class, 'storeVrf'])->name('device.edit.routing.vrf.store');
    Route::patch('/device/{device}/edit/routing/vrf/{vrf}', [Device\EditRoutingController::class, 'updateVrf'])->name('device.edit.routing.vrf.update')->scopeBindings()->whereNumber('vrf');
    Route::delete('/device/{device}/edit/routing/vrf/{vrf}', [Device\EditRoutingController::class, 'destroyVrf'])->name('device.edit.routing.vrf.destroy')->scopeBindings()->whereNumber('vrf');
    Route::post('/device/{device}/edit/routing/peer/{bgpPeer}', [Device\EditRoutingController::class, 'updatePeer'])->name('device.edit.routing.peer.update')->scopeBindings();
    Route::get('/device/{device}/edit/services', Device\EditServicesController::class)->name('device.edit.services');
    Route::get('/device/{device}/edit/storage', [Device\EditStorageController::class, 'index'])->name('device.edit.storage');
    Route::post('/device/{device}/edit/storage/{storage}', [Device\EditStorageController::class, 'update'])->name('device.edit.storage.update')->whereNumber('storage');
    Route::get('/device/{device}/edit/wireless-sensors', [Device\EditWirelessSensorsController::class, 'index'])->name('device.edit.wireless-sensors');
    Route::post('/device/{device}/edit/wireless-sensors/reset', [Device\EditWirelessSensorsController::class, 'reset'])->name('device.edit.wireless-sensors.reset');
    Route::post('/device/{device}/edit/wireless-sensors/{wirelessSensor}', [Device\EditWirelessSensorsController::class, 'update'])->name('device.edit.wireless-sensors.update')->scopeBindings()->whereNumber('wirelessSensor');
    Route::post('/device/{device}/rediscover', [DeviceController::class, 'rediscover'])->name('device.rediscover');

    Route::get('/device/delete', [DeviceController::class, 'deleteIndex'])->name('device.delete');
    Route::prefix('device/{device}')->name('device.')->group(function (): void {
        Route::get('delete', [DeviceController::class, 'deleteConfirm'])->name('delete.confirm');
        Route::delete('', [DeviceController::class, 'destroy'])->name('destroy');

        Route::get('', [DeviceController::class, 'index'])->whereNumber('device')->name('overview');
        foreach (['accesspoints', 'capture', 'config', 'inventory', 'latency', 'mef', 'munin', 'nac', 'notes', 'printer', 'processes', 'services', 'tnmsne', 'vlans', 'vm'] as $tab) {
            Route::get($tab, [DeviceController::class, 'index'])
                ->whereNumber('device')->defaults('tab', $tab)->name($tab);
        }

        Route::get('packages', [DeviceController::class, 'index'])
            ->whereNumber('device')->defaults('tab', 'packages')->middleware('saved-filter:device.packages')->name('packages');
        Route::get('ports', [DeviceController::class, 'index'])
            ->whereNumber('device')->defaults('tab', 'ports')->middleware('saved-filter:device.ports')->name('ports');
        Route::get('ports/portsecurity', [DeviceController::class, 'index'])
            ->whereNumber('device')->defaults('tab', 'ports')->defaults('view', 'portsecurity')
            ->middleware('saved-filter:device.port-security')->name('ports.security');

        Route::get('faults', [Device\Tabs\FaultsController::class, 'index'])
            ->middleware('saved-filter:device.faults')->name('faults');
        Route::get('faults/history', [Device\Tabs\FaultsController::class, 'history'])
            ->middleware('saved-filter:device.alert-log')->name('faults.history');
        Route::get('faults/stats', [Device\Tabs\FaultsController::class, 'stats'])->name('faults.stats');
        Route::get('alerts/{vars?}', [Device\Tabs\FaultsController::class, 'legacy'])->where('vars', '.*')->name('faults.legacy');
        Route::get('alert-stats', [Device\Tabs\FaultsController::class, 'legacy']);
        Route::redirect('logs', 'logs/eventlog')->name('logs');
        Route::get('logs/eventlog', [LogController::class, 'eventlog'])->middleware('saved-filter:device.eventlog')->name('eventlog');
        Route::get('logs/graylog', [LogController::class, 'graylog'])->name('graylog');
        Route::get('logs/outages', Device\Tabs\OutagesController::class)->name('outages');
        Route::get('logs/syslog', [LogController::class, 'syslog'])->middleware('saved-filter:device.syslog')->name('syslog');
        Route::get('apps', [Device\Tabs\AppsController::class, 'index'])->name('apps')->whereNumber('device');
        Route::get('graphs', [Device\Tabs\GraphsController::class, 'index'])->name('graphs')->whereNumber('device');
        Route::get('health', [Device\Tabs\HealthController::class, 'index'])->name('health')->whereNumber('device');
        Route::get('loadbalancer', [Device\Tabs\LoadBalancerController::class, 'index'])->name('loadbalancer')->whereNumber('device');
        Route::get('neighbours', [Device\Tabs\NeighboursController::class, 'index'])->name('neighbours')->whereNumber('device');
        Route::get('netflow', [Device\Tabs\NetflowController::class, 'index'])->name('netflow')->whereNumber('device');
        Route::get('port/{port}', [Device\Tabs\PortController::class, 'show'])->name('port')->scopeBindings()->whereNumber(['device', 'port']);
        Route::get('qos', [Device\Tabs\QosController::class, 'index'])->name('qos')->whereNumber('device');
        Route::get('pseudowires', [Device\Tabs\PseudowiresController::class, 'index'])->name('pseudowires')->whereNumber('device');
        Route::get('slas', [Device\Tabs\SlasController::class, 'index'])->name('slas')->whereNumber('device');
        Route::get('slas/{sla}', [Device\Tabs\SlasController::class, 'show'])->name('slas.show')->scopeBindings()->whereNumber(['device', 'sla']);
        Route::get('stp', [Device\Tabs\StpController::class, 'index'])->name('stp')->whereNumber('device');
        Route::get('wireless', [Device\Tabs\WirelessController::class, 'index'])->name('wireless')->whereNumber('device');
        Route::get('routing', [Device\Tabs\RoutingController::class, 'index'])->name('routing')->whereNumber('device');
        Route::prefix('routing')->name('routing.')->group(function (): void {
            Route::get('bgp', Device\Tabs\Routing\BgpController::class)->name('bgp');
            Route::get('cef', Device\Tabs\Routing\CefController::class)->name('cef');
            Route::get('cisco-otv', Device\Tabs\Routing\CiscoOtvController::class)->name('cisco-otv');
            Route::get('ipsec-tunnels', Device\Tabs\Routing\IpsecTunnelsController::class)->name('ipsec-tunnels');
            Route::get('isis', Device\Tabs\Routing\IsisController::class)->name('isis');
            Route::get('mpls', Device\Tabs\Routing\MplsController::class)->name('mpls');
            Route::get('ospf', Device\Tabs\Routing\OspfController::class)->name('ospf');
            Route::get('ospfv3', Device\Tabs\Routing\Ospfv3Controller::class)->name('ospfv3');
            Route::get('routes', Device\Tabs\Routing\RoutesController::class)->name('routes');
            Route::get('vrf', Device\Tabs\Routing\VrfController::class)->name('vrf');
        });
        Route::get('popup', App\Http\Controllers\DevicePopupController::class)->name('popup');
        Route::get('discovery-status', [DeviceController::class, 'discoveryStatus'])->name('discovery-status');
        Route::put('notes', [Device\Tabs\NotesController::class, 'update'])->name('notes.update');
        Route::post('jobs/monitor', [Device\JobMonitorController::class, 'store'])->name('jobs.monitor');
        Route::delete('jobs/monitor', [Device\JobMonitorController::class, 'destroy'])->name('jobs.stop-monitoring');
        Route::get('config/backups', [Device\Tabs\ConfigController::class, 'backups'])->name('config.backups');
        Route::get('config/backup', [Device\Tabs\ConfigController::class, 'backup'])->name('config.backup');
        Route::get('config/diff', [Device\Tabs\ConfigController::class, 'diff'])->name('config.diff');
        Route::post('config/refresh', [Device\Tabs\ConfigController::class, 'refresh'])->name('config.refresh');
        Route::get('accesspoints/{accessPoint}', [Device\Tabs\AccessPointsController::class, 'show'])->name('accesspoints.show')->scopeBindings();
        Route::get('vm/{vminfo}', [Device\Tabs\VmInfoController::class, 'show'])->name('vm.show')->whereNumber('vminfo');
        Route::get('debug/process', Device\Debug\DebugPollAndDiscoveryController::class)->name('debug.process');
        Route::get('debug/snmp', Device\Debug\DebugSnmpwalkController::class)->name('debug.snmp');
        Route::get('debug/alerts', Device\Debug\DebugAlertsController::class)->name('debug.alerts');
    });

    // legacy device urls (/device/device=1/tab=ports/, /device/hostname, /device/1/ports/detail)
    Route::get('device/{device}/{tab?}/{vars?}', [DeviceController::class, 'legacy'])->where('vars', '.*')->name('device.legacy');

    // Maps
    Route::get('fullscreenmap', [Maps\FullscreenMapController::class, 'fullscreenMap'])->name('maps.fullscreen');
    Route::get('availability-map', [Maps\AvailabilityMapController::class, 'availabilityMap'])->name('maps.availability');
    Route::get('map/{vars?}', [Maps\NetMapController::class, 'netMap'])->name('maps.network');
    Route::prefix('maps')->group(function (): void {
        Route::resource('custom', CustomMapController::class, ['as' => 'maps'])
            ->parameters(['custom' => 'map'])->except('create');
        Route::post('custom/{map}/clone', [CustomMapController::class, 'clone'])->name('maps.custom.clone');
        Route::get('custom/{map}/background', [CustomMapBackgroundController::class, 'get'])->name('maps.custom.background');
        Route::post('custom/{map}/background', [CustomMapBackgroundController::class, 'save'])->name('maps.custom.background.save');
        Route::get('custom/{map}/data', [CustomMapDataController::class, 'get'])->name('maps.custom.data');
        Route::post('custom/{map}/data', [CustomMapDataController::class, 'save'])->name('maps.custom.data.save');
        Route::get('customlist', [CustomMapListController::class, 'index'])->name('maps.custom.list');
        Route::get('devicedependency', [Maps\DeviceDependencyController::class, 'dependencyMap'])->name('maps.devicedependency');
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

    // dashboard
    Route::resource('dashboard', DashboardController::class)->except(['create', 'edit']);
    Route::post('dashboard/{dashboard}/copy', [DashboardController::class, 'copy'])->name('dashboard.copy');
    Route::post('dashboard/{dashboard}/widgets', [DashboardWidgetController::class, 'add'])->name('dashboard.widget.add');
    Route::delete('dashboard/{dashboard}/widgets', [DashboardWidgetController::class, 'clear'])->name('dashboard.widget.clear');
    Route::put('dashboard/{dashboard}/widgets', [DashboardWidgetController::class, 'update'])->name('dashboard.widget.update');
    Route::delete('dashboard/widgets/{widget}', [DashboardWidgetController::class, 'remove'])->name('dashboard.widget.remove');
    Route::put('dashboard/widgets/{widget}', [WidgetSettingsController::class, 'update'])->name('dashboard.widget.settings');

    Route::get('tool/oui-lookup', OuiLookupController::class)->name('tool.oui-lookup');
    Route::get('tool/ripe', RipeNccApiController::class)->name('tool.ripe');
    Route::redirect('ripenccapi', 'tool/ripe');

    // Notifications
    Route::prefix('notifications')->name('notifications.')->whereNumber('notification')->group(function (): void {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::get('archive', [NotificationController::class, 'archive'])->name('archive');
        Route::post('/', [NotificationController::class, 'store'])->name('store');
        Route::put('read-all', [NotificationController::class, 'readAll'])->name('read-all');
        Route::put('{notification}/read', [NotificationController::class, 'read'])->name('read');
        Route::put('{notification}/stick', [NotificationController::class, 'stick'])->name('stick');
        Route::delete('{notification}/stick', [NotificationController::class, 'unstick'])->name('unstick');
    });

    // Push notifications
    Route::prefix('push')->group(function (): void {
        Route::get('token', [PushNotificationController::class, 'token'])->name('push.token');
        Route::get('key', [PushNotificationController::class, 'key'])->name('push.key');
        Route::post('register', [PushNotificationController::class, 'register'])->name('push.register');
        Route::post('unregister', [PushNotificationController::class, 'unregister'])->name('push.unregister');
    });

    Route::get('alert-operations', [AlertOperationController::class, 'index'])->name('alert-operations.index');
    // admin pages
    Route::get('settings/{tab?}/{section?}/{setting?}', [SettingsController::class, 'index'])->name('settings');
    Route::put('settings/{name}', [SettingsController::class, 'update'])->name('settings.update');
    Route::delete('settings/{name}', [SettingsController::class, 'destroy'])->name('settings.destroy');

    Route::resource('roles', RoleController::class);

    Route::post('alert/transports/{transport}/test', [AlertTransportController::class, 'test'])->name('alert.transports.test');
    Route::get('alert-transports', [AlertTransportController::class, 'index'])->name('alert-transports.index');
    Route::resource('alert/transports', AlertTransportController::class)->only(['show', 'store', 'update', 'destroy'])
        ->names('alert-transports')->parameters(['transports' => 'transport']);
    Route::resource('alert/transport-groups', AlertTransportGroupController::class)->only(['show', 'store', 'update', 'destroy'])
        ->names('alert-transport-groups')->parameters(['transport-groups' => 'alert_transport_group']);
    Route::post('alert-templates/convert', [AlertTemplateController::class, 'convert'])->name('alert-templates.convert');
    Route::resource('alert-templates', AlertTemplateController::class)->except(['show'])->where(['alert_template' => '[0-9]+']);
    Route::redirect('templates', 'alert-templates');
    Route::get('alert-rules', [AlertRuleController::class, 'index'])->name('alert-rules.index');
    Route::post('alert-rules/defaults', [AlertRuleController::class, 'createDefaults'])->name('alert-rules.defaults');
    Route::resource('alert-rule', AlertRuleController::class)->except(['index']);
    Route::resource('alert-operation', AlertOperationController::class)->only(['show', 'store', 'update', 'destroy']);
    Route::put('alert-rule/{alert_rule}/toggle', [AlertRuleController::class, 'toggle'])->name('alert-rule.toggle');
    Route::get('alert-rule-from-template/{template_id}', [AlertRuleTemplateController::class, 'template'])->name('alert-rule-template');
    Route::get('alert-rule-from-rule/{alert_rule}', [AlertRuleTemplateController::class, 'rule'])->name('alert-rule-template.rule');
    Route::get('alertlog/{alertLog}/details', Ajax\AlertDetailsController::class)->name('alertlog.details');

    Route::resource('port-groups', PortGroupController::class);
    Route::get('port-groups/{port_group}/ports', [PortGroupPortsController::class, 'index'])->name('port-groups.ports')->middleware('saved-filter:port-group.ports');
    Route::get('port-groups/{port_group}/ports/list', [PortGroupPortsController::class, 'ports'])->name('port-groups.ports.list');
    Route::post('port-groups/{port_group}/ports', [PortGroupPortsController::class, 'update'])->name('port-groups.ports.update');
    Route::get('validate', [ValidateController::class, 'index'])->name('validate');
    Route::get('validate/results/{group?}', [ValidateController::class, 'runValidation'])->name('validate.results');
    Route::post('validate/fix', [ValidateController::class, 'runFixer'])->name('validate.fix');

    Route::middleware(['can:plugin.admin'])->group(function (): void {
        Route::get('plugin/settings', App\Http\Controllers\PluginAdminController::class)->name('plugin.admin');
        Route::get('plugin/settings/{plugin:plugin_name}', PluginSettingsController::class)->name('plugin.settings');
        Route::post('plugin/settings/{plugin:plugin_name}', [PluginSettingsController::class, 'update'])->name('plugin.update');
    });

    Route::get('plugin', [PluginLegacyController::class, 'redirect']);
    Route::get('plugin/p={pluginName}', [PluginLegacyController::class, 'redirect']);
    Route::any('plugin/v1/{plugin:plugin_name}/{other?}', PluginLegacyController::class)->where('other', '(.*)')->name('plugin.legacy');
    Route::get('plugin/{plugin:plugin_name}', PluginPageController::class)->name('plugin.page');

    Route::get('health/{metric?}/{legacyview?}', [SensorController::class, 'index'])->name('sensor.index');
    Route::get('wireless/{metric?}/{legacyview?}', [WirelessSensorController::class, 'index'])->name('wireless.index');

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
        Route::resource('pollergroup', PollerGroupController::class)->only('destroy', 'show', 'store', 'update');
        Route::resource('alert-schedule', AlertScheduleController::class)->only('show', 'edit', 'store', 'update', 'destroy');
        Route::post('alert-schedule/{alert_schedule}/end', [AlertScheduleController::class, 'end'])->name('alert-schedule.end');
        Route::put('device-dependencies', [DeviceDependencyController::class, 'update'])->name('device-dependencies.update');
        Route::delete('device-dependencies', [DeviceDependencyController::class, 'destroy'])->name('device-dependencies.destroy');
        // misc ajax controllers
        Route::get('search/devices', Ajax\Search\DevicesSearchController::class)->name('ajax.search.devices');
        Route::get('search/ports', Ajax\Search\PortsSearchController::class)->name('ajax.search.ports');
        Route::get('search/fdb', Ajax\Search\FdbSearchController::class)->name('ajax.search.fdb');
        Route::get('search/arp', Ajax\Search\ArpSearchController::class)->name('ajax.search.arp');
        Route::get('search/health', Ajax\Search\HealthSearchController::class)->name('ajax.search.health');
        Route::get('search/routing', Ajax\Search\RoutingSearchController::class)->name('ajax.search.routing');
        Route::get('search/logs', Ajax\Search\LogsSearchController::class)->name('ajax.search.logs');
        Route::post('set_resolution', [Ajax\SessionController::class, 'resolution'])->name('ajax.set-resolution');
        Route::post('set_style', [Ajax\SessionController::class, 'style'])->name('ajax.set-style');
        Route::post('template/preview', Ajax\TemplatePreviewController::class)->name('ajax.template.preview');

        // js select2 data controllers
        Route::prefix('select')->group(function (): void {
            Route::get('alert-transport', Select\AlertTransportController::class)->name('ajax.select.alert-transport');
            Route::get('alert-transport-group', Select\AlertTransportGroupController::class)->name('ajax.select.alert-transport-group');
            Route::get('alert-transports-groups', Select\AlertTransportsAndGroupsController::class)->name('ajax.select.alert-transports-groups');
            Route::get('alert-operation', Select\AlertOperationController::class)->name('ajax.select.alert-operation');
            Route::get('alert-rule', Select\AlertRuleController::class)->name('ajax.select.alert-rule');
            Route::get('alert-log-rule', Select\AlertLogRuleController::class)->name('ajax.select.alert-log-rule');
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
            Route::get('secret', Select\SecretController::class)->name('ajax.select.secret');
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
            Route::post('alertlog', Table\AlertLogController::class)->name('table.alertlog');
            Route::get('alertlog/export', [Table\AlertLogController::class, 'export'])->name('table.alertlog.export');
            Route::post('alertlog-stats', Table\AlertLogStatsController::class)->name('table.alertlog-stats');
            Route::post('alerts', Table\AlertsController::class)->name('table.alerts');
            Route::post('arp-search', Table\ArpTableController::class)->name('table.arp-search');
            Route::post('bills', Table\BillsController::class)->name('table.bills');
            Route::post('alert-schedule', Table\AlertScheduleController::class)->name('table.alert-schedule');
            Route::post('access-points', Table\AccessPointController::class)->name('table.access-points');
            Route::post('customers', Table\CustomersController::class)->name('table.customers');
            Route::post('diskio', Table\DiskioController::class)->name('table.diskio');
            Route::post('device', Table\DeviceController::class)->name('table.device');
            Route::post('device-dependencies', Table\DeviceDependenciesController::class)->name('table.device-dependencies');
            Route::get('device/export', [Table\DeviceController::class, 'export'])->name('table.device.export');
            Route::post('eventlog', Table\EventlogController::class)->name('table.eventlog');
            Route::post('fdb-tables', Table\FdbTablesController::class)->name('table.fdb-tables');
            Route::post('graylog', Table\GraylogController::class)->name('table.graylog');
            Route::post('inventory', Table\InventoryController::class)->name('table.inventory');
            Route::get('inventory/export', [Table\InventoryController::class, 'export'])->name('table.inventory.export');
            Route::post('location', Table\LocationController::class)->name('table.location');
            Route::get('location/export', [Table\LocationController::class, 'export'])->name('table.location.export');
            Route::post('mempools', Table\MempoolsController::class)->name('table.mempools');
            Route::get('mempools/export', [Table\MempoolsController::class, 'export'])->name('table.mempools.export');
            Route::post('outages', Table\OutagesController::class)->name('table.outages');
            Route::get('outages/export', [Table\OutagesController::class, 'export'])->name('table.outages.export');
            Route::post('poll-log', Table\PollLogController::class)->name('table.poll-log');
            Route::post('port-nac', Table\PortNacController::class)->name('table.port-nac');
            Route::get('port-security/export', [Table\PortSecurityController::class, 'export'])->name('port-security.export');
            Route::post('port-stp', Table\PortStpController::class)->name('table.port-stp');
            Route::post('ports', Table\PortsController::class)->name('table.ports');
            Route::get('ports/export', [Table\PortsController::class, 'export'])->name('table.ports.export');
            Route::post('processors', Table\ProcessorsController::class)->name('table.processors');
            Route::get('processors/export', [Table\ProcessorsController::class, 'export'])->name('table.processors.export');
            Route::post('routes', Table\RoutesTablesController::class)->name('table.routes');
            Route::post('sensors', Table\SensorsController::class)->name('table.sensors');
            Route::get('sensors/export', [Table\SensorsController::class, 'export'])->name('table.sensors.export');
            Route::post('storages', Table\StoragesController::class)->name('table.storages');
            Route::get('storages/export', [Table\StoragesController::class, 'export'])->name('table.storages.export');
            Route::post('syslog', Table\SyslogController::class)->name('table.syslog');
            Route::post('printer-supply', Table\PrinterSupplyController::class)->name('table.printer-supply');
            Route::post('tnmsne', Table\TnmsneController::class)->name('table.tnmsne');
            Route::post('wireless', Table\WirelessSensorController::class)->name('table.wireless');
            Route::post('vlan-ports', Table\VlanPortsController::class)->name('table.vlan-ports');
            Route::post('vlan-devices', Table\VlanDevicesController::class)->name('table.vlan-devices');
            Route::post('vminfo', Table\VminfoController::class)->name('table.vminfo');
            Route::get('vminfo/export', [Table\VminfoController::class, 'export'])->name('table.vminfo.export');
            Route::post('ssl-certificates', Table\SslCertificateController::class)->name('table.ssl-certificates');
        });

        // dashboard widgets
        Route::prefix('dash')->name('widget.')->group(function (): void {
            Route::post('alerts', Widgets\AlertsController::class)->name('alerts');
            Route::post('alertlog', Widgets\AlertlogController::class)->name('alertlog');
            Route::post('alert-map', Widgets\AlertMapController::class)->name('alert-map');
            Route::post('alertlog-stats', Widgets\AlertlogStatsController::class)->name('alertlog-stats');
            Route::post('availability-map', Widgets\AvailabilityMapController::class)->name('availability-map');
            Route::post('component-status', Widgets\ComponentStatusController::class)->name('component-status');
            Route::post('custom-map', Widgets\CustomMapController::class)->name('custom-map');
            Route::post('device-summary-horiz', Widgets\DeviceSummaryHorizController::class)->name('device-summary-horiz');
            Route::post('device-summary-vert', Widgets\DeviceSummaryVertController::class)->name('device-summary-vert');
            Route::post('device-types', Widgets\DeviceTypeController::class)->name('device-types');
            Route::post('eventlog', Widgets\EventlogController::class)->name('eventlog');
            Route::post('generic-graph', Widgets\GraphController::class)->name('generic-graph');
            Route::post('generic-image', Widgets\ImageController::class)->name('generic-image');
            Route::post('globe', Widgets\GlobeController::class)->name('globe');
            Route::post('graylog', Widgets\GraylogController::class)->name('graylog');
            Route::post('health-sensors', Widgets\HealthSensorsController::class)->name('health-sensors');
            Route::post('placeholder', Widgets\PlaceholderController::class)->name('placeholder');
            Route::post('notes', Widgets\NotesController::class)->name('notes');
            Route::post('server-stats', Widgets\ServerStatsController::class)->name('server-stats');
            Route::post('syslog', Widgets\SyslogController::class)->name('syslog');
            Route::post('top-devices', Widgets\TopDevicesController::class)->name('top-devices');
            Route::post('top-interfaces', Widgets\TopInterfacesController::class)->name('top-interfaces');
            Route::post('top-errors', Widgets\TopErrorsController::class)->name('top-errors');
            Route::post('worldmap', Widgets\WorldMapController::class)->name('worldmap');
        });
    });

    // demo helper
    Route::permanentRedirect('demo', '/');

    // session and auth boot for standalone v1 plugin scripts, see Laravel::bootWeb()
    Route::get('legacy-plugin-boot', fn () => response()->noContent())->name('plugin.legacy-boot');
});

// routes that don't need authentication
Route::prefix('ajax')->group(function (): void {
    Route::post('set_timezone', [Ajax\TimezoneController::class, 'set'])->name('ajax.set-timezone');
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
});
