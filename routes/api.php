<?php

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::prefix('v0')->group(function (): void {
    Route::get('ping', fn () => response()->json(['message' => 'pong']))->name('ping');
    Route::get('system', [App\Api\Controllers\LegacyApiController::class, 'server_info'])->name('server_info');
    Route::get('', [App\Api\Controllers\LegacyApiController::class, 'show_endpoints']);

    // Alert Templates
    Route::middleware(['can:viewAny,App\Models\AlertTemplate'])->group(function (): void {
        Route::get('alert_templates/{id}', [App\Api\Controllers\LegacyApiController::class, 'list_alert_templates'])->name('get_alert_template');
        Route::get('alert_templates', [App\Api\Controllers\LegacyApiController::class, 'list_alert_templates'])->name('list_alert_templates');
        Route::post('alert_templates', [App\Api\Controllers\LegacyApiController::class, 'add_edit_alert_template'])->name('add_alert_template')->middleware('can:create,App\Models\AlertTemplate');
        Route::put('alert_templates', [App\Api\Controllers\LegacyApiController::class, 'add_edit_alert_template'])->name('edit_alert_template')->middleware('can:update,App\Models\AlertTemplate');
    });

    // Pollers
    Route::middleware(['can:viewAny,App\Models\Poller'])->group(function (): void {
        Route::get('pollers', [App\Api\Controllers\LegacyApiController::class, 'list_pollers'])->name('list_pollers');
    });

    // Pollers Log
    Route::middleware(['can:viewAny,App\Models\Device'])->group(function (): void {
        Route::get('pollers/log', [App\Api\Controllers\LegacyApiController::class, 'list_poller_log'])->name('list_poller_log');
    });

    // BGP
    Route::middleware(['can:viewAny,App\Models\BgpPeer'])->group(function (): void {
        Route::get('bgp', [App\Api\Controllers\LegacyApiController::class, 'list_bgp'])->name('list_bgp');
        Route::get('bgp/{id}', [App\Api\Controllers\LegacyApiController::class, 'get_bgp'])->name('get_bgp');
        Route::post('bgp/{id}', [App\Api\Controllers\LegacyApiController::class, 'edit_bgp_descr'])->name('edit_bgp_descr')->middleware('can:update,App\Models\BgpPeer');
    });

    // OSPF / Routing
    Route::middleware(['can:viewAny,App\Models\Route'])->group(function (): void {
        Route::get('ospf', [App\Api\Controllers\LegacyApiController::class, 'list_ospf'])->name('list_ospf');
        Route::get('ospf_ports', [App\Api\Controllers\LegacyApiController::class, 'list_ospf_ports'])->name('list_ospf_ports');
        Route::get('ospfv3', [App\Api\Controllers\LegacyApiController::class, 'list_ospfv3'])->name('list_ospfv3');
        Route::get('ospfv3_ports', [App\Api\Controllers\LegacyApiController::class, 'list_ospfv3_ports'])->name('list_ospfv3_ports');
    });

    // Oxidized
    Route::middleware(['can:showConfig,App\Models\Device'])->group(function (): void {
        Route::get('oxidized/{hostname?}', [App\Api\Controllers\LegacyApiController::class, 'list_oxidized'])->name('list_oxidized');
        Route::get('oxidized/config/search/{searchstring}', [App\Api\Controllers\LegacyApiController::class, 'search_oxidized'])->name('search_oxidized');
        Route::get('oxidized/config/{device_name}', [App\Api\Controllers\LegacyApiController::class, 'get_oxidized_config'])->name('get_oxidized_config');
    });

    // Device Groups
    Route::middleware(['can:viewAny,App\Models\DeviceGroup'])->group(function (): void {
        Route::get('devicegroups/{name}', [App\Api\Controllers\LegacyApiController::class, 'get_devices_by_group'])->name('get_devices_by_group');
        Route::get('devicegroups', [App\Api\Controllers\LegacyApiController::class, 'get_device_groups'])->name('get_device_groups');
        Route::post('devicegroups', [App\Api\Controllers\LegacyApiController::class, 'add_device_group'])->name('add_device_group')->middleware('can:create,App\Models\DeviceGroup');
        Route::patch('devicegroups/{name}', [App\Api\Controllers\LegacyApiController::class, 'update_device_group'])->name('update_device_group')->middleware('can:update,App\Models\DeviceGroup');
        Route::delete('devicegroups/{name}', [App\Api\Controllers\LegacyApiController::class, 'delete_device_group'])->name('delete_device_group')->middleware('can:delete,App\Models\DeviceGroup');
        Route::post('devicegroups/{name}/devices', [App\Api\Controllers\LegacyApiController::class, 'update_device_group_add_devices'])->name('update_device_group_add_devices')->middleware('can:update,App\Models\DeviceGroup');
        Route::delete('devicegroups/{name}/devices', [App\Api\Controllers\LegacyApiController::class, 'update_device_group_remove_devices'])->name('update_device_group_remove_devices')->middleware('can:update,App\Models\DeviceGroup');
        Route::post('devicegroups/{name}/maintenance', [App\Api\Controllers\LegacyApiController::class, 'maintenance_devicegroup'])->name('maintenance_devicegroup')->middleware('can:update,App\Models\DeviceGroup');
    });

    // Port Groups
    Route::middleware(['can:viewAny,App\Models\PortGroup'])->group(function (): void {
        Route::get('port_groups', [App\Api\Controllers\LegacyApiController::class, 'get_port_groups'])->name('get_port_groups');
        Route::get('port_groups/{name}', [App\Api\Controllers\LegacyApiController::class, 'get_ports_by_group'])->name('get_ports_by_group');
        Route::get('portgroups/multiport/bits/{id}', [App\Api\Controllers\LegacyApiController::class, 'get_graph_by_portgroup'])->name('get_graph_by_portgroup_multiport_bits');
        Route::get('portgroups/{group}', [App\Api\Controllers\LegacyApiController::class, 'get_graph_by_portgroup'])->name('get_graph_by_portgroup');
        Route::post('port_groups', [App\Api\Controllers\LegacyApiController::class, 'add_port_group'])->name('add_port_group')->middleware('can:create,App\Models\PortGroup');
        Route::post('port_groups/{port_group_id}/assign', [App\Api\Controllers\LegacyApiController::class, 'assign_port_group'])->name('assign_port_group')->middleware('can:update,App\Models\PortGroup');
        Route::post('port_groups/{port_group_id}/remove', [App\Api\Controllers\LegacyApiController::class, 'remove_port_group'])->name('remove_port_group')->middleware('can:update,App\Models\PortGroup');
    });

    // Alerts
    Route::middleware(['can:viewAny,App\Models\Alert'])->group(function (): void {
        Route::get('alerts/{id}', [App\Api\Controllers\LegacyApiController::class, 'list_alerts'])->name('get_alert');
        Route::get('alerts', [App\Api\Controllers\LegacyApiController::class, 'list_alerts'])->name('list_alerts');
        Route::put('alerts/{id}', [App\Api\Controllers\LegacyApiController::class, 'ack_alert'])->name('ack_alert')->middleware('can:update,App\Models\Alert');
        Route::put('alerts/unmute/{id}', [App\Api\Controllers\LegacyApiController::class, 'unmute_alert'])->name('unmute_alert')->middleware('can:update,App\Models\Alert');
    });

    // Alert Rules
    Route::middleware(['can:viewAny,App\Models\AlertRule'])->group(function (): void {
        Route::get('rules/{id}', [App\Api\Controllers\LegacyApiController::class, 'list_alert_rules'])->name('get_alert_rule');
        Route::get('rules', [App\Api\Controllers\LegacyApiController::class, 'list_alert_rules'])->name('list_alert_rules');
        Route::post('rules', [App\Api\Controllers\LegacyApiController::class, 'add_edit_rule'])->name('add_rule')->middleware('can:create,App\Models\AlertRule');
        Route::put('rules', [App\Api\Controllers\LegacyApiController::class, 'add_edit_rule'])->name('edit_rule')->middleware('can:update,App\Models\AlertRule');
        Route::delete('rules/{id}', [App\Api\Controllers\LegacyApiController::class, 'delete_rule'])->name('delete_rule')->middleware('can:delete,App\Models\AlertRule');
    });

    // Routing VRF
    Route::middleware(['can:viewAny,App\Models\Vrf'])->group(function (): void {
        Route::get('routing/vrf/{id}', [App\Api\Controllers\LegacyApiController::class, 'get_vrf'])->name('get_vrf');
    });

    // Routing IPSec / Devices / General Device maintenance
    Route::middleware(['can:viewAny,App\Models\Device'])->group(function (): void {
        Route::get('routing/ipsec/data/{hostname}', [App\Api\Controllers\LegacyApiController::class, 'list_ipsec'])->name('list_ipsec');
    });

    // Services
    Route::middleware(['can:viewAny,App\Models\Service'])->group(function (): void {
        Route::get('services', [App\Api\Controllers\LegacyApiController::class, 'list_services'])->name('list_services');
        Route::get('services/{hostname}', [App\Api\Controllers\LegacyApiController::class, 'list_services'])->name('list_services_device');
        Route::post('services/{hostname}', [App\Api\Controllers\LegacyApiController::class, 'add_service_for_host'])->name('add_service_for_host')->middleware('can:create,App\Models\Service');
        Route::delete('services/{id}', [App\Api\Controllers\LegacyApiController::class, 'del_service_from_host'])->name('del_service_from_host')->middleware('can:delete,App\Models\Service');
        Route::patch('services/{id}', [App\Api\Controllers\LegacyApiController::class, 'edit_service_for_host'])->name('edit_service_for_host')->middleware('can:update,App\Models\Service');
    });

    // Syslog Sink (receives syslogs, requires write access on devices / system)
    Route::post('syslogsink', [App\Api\Controllers\LegacyApiController::class, 'post_syslogsink'])->name('post_syslogsink')->middleware('can:update,App\Models\Device');

    // Poller Groups
    Route::get('poller_group/{poller_group_id_or_name?}', [App\Api\Controllers\LegacyApiController::class, 'get_poller_group'])->name('get_poller_group')->middleware('can:viewAny,App\Models\PollerGroup');

    // Device actions / details (restricted by access)
    Route::prefix('devices')->group(function (): void {
        Route::middleware('can:viewAny,App\Models\Device')->group(function (): void {
            Route::get('{hostname}', [App\Api\Controllers\LegacyApiController::class, 'get_device'])->name('get_device');
            Route::get('{hostname}/discover', [App\Api\Controllers\LegacyApiController::class, 'trigger_device_discovery'])->name('trigger_device_discovery');
            Route::get('{hostname}/availability', [App\Api\Controllers\LegacyApiController::class, 'device_availability'])->name('device_availability');
            Route::get('{hostname}/outages', [App\Api\Controllers\LegacyApiController::class, 'device_outages'])->name('device_outages');
            Route::get('{hostname}/graphs/health/{type}/{sensor_id?}', [App\Api\Controllers\LegacyApiController::class, 'get_graph_generic_by_hostname'])->name('get_health_graph');
            Route::get('{hostname}/graphs/wireless/{type}/{sensor_id?}', [App\Api\Controllers\LegacyApiController::class, 'get_graph_generic_by_hostname'])->name('get_wireless_graph');
            Route::get('{hostname}/vlans', [App\Api\Controllers\LegacyApiController::class, 'get_vlans'])->name('get_vlans');
            Route::get('{hostname}/links', [App\Api\Controllers\LegacyApiController::class, 'list_links'])->name('list_links_device');
            Route::get('{hostname}/graphs', [App\Api\Controllers\LegacyApiController::class, 'get_graphs'])->name('get_graphs');
            Route::get('{hostname}/fdb', [App\Api\Controllers\LegacyApiController::class, 'get_fdb'])->name('get_fdb');
            Route::get('{hostname}/nac', [App\Api\Controllers\LegacyApiController::class, 'get_nac'])->name('get_nac');
            Route::get('{hostname}/health/{type?}/{sensor_id?}', [App\Api\Controllers\LegacyApiController::class, 'list_available_health_graphs'])->name('list_available_health_graphs');
            Route::get('{hostname}/wireless/{type?}/{sensor_id?}', [App\Api\Controllers\LegacyApiController::class, 'list_available_wireless_graphs'])->name('list_available_wireless_graphs');
            Route::get('{hostname}/wireless-sensors', [App\Api\Controllers\LegacyApiController::class, 'get_device_wireless_sensors'])->name('get_device_wireless_sensors');
            Route::get('{hostname}/ports', [App\Api\Controllers\LegacyApiController::class, 'get_device_ports'])->name('get_device_ports');
            Route::get('{hostname}/ip', [App\Api\Controllers\LegacyApiController::class, 'get_device_ip_addresses'])->name('get_ip_addresses');
            Route::get('{hostname}/port_stack', [App\Api\Controllers\LegacyApiController::class, 'get_port_stack'])->name('get_port_stack');
            Route::get('{hostname}/transceivers', [App\Api\Controllers\LegacyApiController::class, 'get_transceivers'])->name('get_transceivers');
            Route::get('{hostname}/components', [App\Api\Controllers\LegacyApiController::class, 'get_components'])->name('get_components');
            Route::get('{hostname}/groups', [App\Api\Controllers\LegacyApiController::class, 'get_device_groups'])->name('get_device_groups_device');
            Route::get('{hostname}/maintenance', [App\Api\Controllers\LegacyApiController::class, 'device_under_maintenance'])->name('device_under_maintenance');
            // consumes the route below, but passes to it when detected
            Route::get('{hostname}/ports/{ifname}', [App\Api\Controllers\LegacyApiController::class, 'get_port_stats_by_port_hostname'])->name('get_port_stats_by_port_hostname')->where('ifname', '.*');
            Route::get('{hostname}/ports/{ifname}/{type}', [App\Api\Controllers\LegacyApiController::class, 'get_graph_by_port_hostname'])->name('get_graph_by_port_hostname');
            Route::get('{hostname}/services/{id}/graphs/{datasource}', [App\Api\Controllers\LegacyApiController::class, 'get_graph_by_service'])->name('get_graph_by_service');
            Route::post('{hostname}/eventlog', [App\Api\Controllers\LegacyApiController::class, 'add_eventlog'])->name('add_eventlog');
            Route::get('{hostname}/{type}', [App\Api\Controllers\LegacyApiController::class, 'get_graph_generic_by_hostname'])->name('get_graph_generic_by_hostname');
            Route::get('', [App\Api\Controllers\LegacyApiController::class, 'list_devices'])->name('list_devices');
        });

        Route::middleware('can:create,App\Models\Device')->group(function (): void {
            Route::post('', [App\Api\Controllers\LegacyApiController::class, 'add_device'])->name('add_device');
        });

        Route::middleware('can:delete,App\Models\Device')->group(function (): void {
            Route::delete('{hostname}', [App\Api\Controllers\LegacyApiController::class, 'del_device'])->name('del_device');
        });

        Route::middleware('can:update,App\Models\Device')->group(function (): void {
            Route::patch('{hostname}', [App\Api\Controllers\LegacyApiController::class, 'update_device'])->name('update_device_field');
            Route::patch('{hostname}/rename/{new_hostname}', [App\Api\Controllers\LegacyApiController::class, 'rename_device'])->name('rename_device');
            Route::post('{hostname}/maintenance', [App\Api\Controllers\LegacyApiController::class, 'maintenance_device'])->name('maintenance_device');
            Route::post('{id}/parents', [App\Api\Controllers\LegacyApiController::class, 'add_parents_to_host'])->name('add_parents_to_host');
            Route::delete('{id}/parents', [App\Api\Controllers\LegacyApiController::class, 'del_parents_from_host'])->name('del_parents_from_host');
        });

        Route::middleware('can:create,App\Models\Component')->group(function (): void {
            Route::post('{hostname}/components/{type}', [App\Api\Controllers\LegacyApiController::class, 'add_components'])->name('add_components');
        });

        Route::middleware('can:update,App\Models\Component')->group(function (): void {
            Route::put('{hostname}/components', [App\Api\Controllers\LegacyApiController::class, 'edit_components'])->name('edit_components');
        });

        Route::middleware('can:delete,App\Models\Component')->group(function (): void {
            Route::delete('{hostname}/components/{component}', [App\Api\Controllers\LegacyApiController::class, 'delete_components'])->name('delete_components');
        });
    });

    // Ports
    Route::prefix('ports')->group(function (): void {
        Route::middleware('can:viewAny,App\Models\Port')->group(function (): void {
            Route::get('{portid}', [App\Api\Controllers\LegacyApiController::class, 'get_port_info'])->name('get_port_info');
            Route::get('{portid}/fdb', [App\Api\Controllers\LegacyApiController::class, 'get_port_fdb'])->name('get_port_fdb');
            Route::get('{portid}/ip', [App\Api\Controllers\LegacyApiController::class, 'get_port_ip_addresses'])->name('get_port_ip_info');
            Route::get('{portid}/transceiver', [App\Api\Controllers\LegacyApiController::class, 'get_port_transceiver'])->name('get_port_transceiver');
            Route::get('search/{field}/{search?}', [App\Api\Controllers\LegacyApiController::class, 'search_ports'])->name('search_ports')->where('search', '.*');
            Route::get('mac/{search}', [App\Api\Controllers\LegacyApiController::class, 'search_by_mac'])->name('search_mac');
            Route::get('', [App\Api\Controllers\LegacyApiController::class, 'get_all_ports'])->name('get_all_ports');
            Route::get('{portid}/description', [App\Api\Controllers\LegacyApiController::class, 'get_port_description'])->name('get_port_description');
        });
        Route::middleware('can:update,App\Models\Port')->group(function (): void {
            Route::patch('transceiver/metric/{metric}', [App\Api\Controllers\LegacyApiController::class, 'update_transceiver_metric_thresholds'])->name('update_transceiver_metric_thresholds');
            Route::patch('{portid}/description', [App\Api\Controllers\LegacyApiController::class, 'update_port_description'])->name('update_port_description');
        });
    });

    // Bills
    Route::prefix('bills')->group(function (): void {
        Route::middleware('can:viewAny,App\Models\Bill')->group(function (): void {
            Route::get('', [App\Api\Controllers\LegacyApiController::class, 'list_bills'])->name('list_bills');
            Route::get('{bill_id}', [App\Api\Controllers\LegacyApiController::class, 'list_bills'])->name('get_bill');
            Route::get('{bill_id}/graphs/{graph_type}', [App\Api\Controllers\LegacyApiController::class, 'get_bill_graph'])->name('get_bill_graph');
            Route::get('{bill_id}/graphdata/{graph_type}', [App\Api\Controllers\LegacyApiController::class, 'get_bill_graphdata'])->name('get_bill_graphdata');
            Route::get('{bill_id}/history', [App\Api\Controllers\LegacyApiController::class, 'get_bill_history'])->name('get_bill_history');
            Route::get('{bill_id}/history/{bill_hist_id}/graphs/{graph_type}', [App\Api\Controllers\LegacyApiController::class, 'get_bill_history_graph'])->name('get_bill_history_graph');
            Route::get('{bill_id}/history/{bill_hist_id}/graphdata/{graph_type}', [App\Api\Controllers\LegacyApiController::class, 'get_bill_history_graphdata'])->name('get_bill_history_graphdata');
        });
        Route::middleware('can:create,App\Models\Bill')->group(function (): void {
            Route::post('', [App\Api\Controllers\LegacyApiController::class, 'create_edit_bill'])->name('create_bill');
        });
        Route::middleware('can:delete,App\Models\Bill')->group(function (): void {
            Route::delete('{bill_id}', [App\Api\Controllers\LegacyApiController::class, 'delete_bill'])->name('delete_bill');
        });
    });

    // Routing
    Route::prefix('routing')->middleware('can:viewAny,App\Models\Route')->group(function (): void {
        Route::get('bgp/cbgp', [App\Api\Controllers\LegacyApiController::class, 'list_cbgp'])->name('list_cbgp');
        Route::get('vrf', [App\Api\Controllers\LegacyApiController::class, 'list_vrf'])->name('list_vrf');
        Route::get('mpls/services', [App\Api\Controllers\LegacyApiController::class, 'list_mpls_services'])->name('list_mpls_services');
        Route::get('mpls/saps', [App\Api\Controllers\LegacyApiController::class, 'list_mpls_saps'])->name('list_mpls_saps');
    });

    // Resources
    Route::prefix('resources')->group(function (): void {
        Route::middleware('can:viewAny,App\Models\Port')->group(function (): void {
            Route::get('fdb', [App\Api\Controllers\LegacyApiController::class, 'list_fdb'])->name('list_fdb');
            Route::get('fdb/{mac}', [App\Api\Controllers\LegacyApiController::class, 'list_fdb'])->name('list_fdb_mac');
            Route::get('fdb/{mac}/detail', [App\Api\Controllers\LegacyApiController::class, 'list_fdb_detail'])->name('list_fdb_detail');
            Route::get('ip/addresses/{address_family?}', [App\Api\Controllers\LegacyApiController::class, 'list_ip_addresses'])->name('list_ip_addresses');
            Route::get('ip/arp/{query}/{cidr?}', [App\Api\Controllers\LegacyApiController::class, 'list_arp'])->name('list_arp');
            Route::get('ip/networks/{address_family?}', [App\Api\Controllers\LegacyApiController::class, 'list_ip_networks'])->name('list_ip_networks');
            Route::get('ip/networks/{id}/ip', [App\Api\Controllers\LegacyApiController::class, 'get_network_ip_addresses'])->name('get_network_ip_addresses');
        });
        Route::middleware('can:viewAny,App\Models\Link')->group(function (): void {
            Route::get('links', [App\Api\Controllers\LegacyApiController::class, 'list_links'])->name('list_links');
            Route::get('links/{id}', [App\Api\Controllers\LegacyApiController::class, 'get_link'])->name('get_link');
        });
        Route::middleware('can:viewAny,App\Models\PortsNac')->group(function (): void {
            Route::get('nac', [App\Api\Controllers\LegacyApiController::class, 'list_nac'])->name('list_nac');
            Route::get('nac/{mac}', [App\Api\Controllers\LegacyApiController::class, 'list_nac'])->name('list_nac_mac');
        });
        Route::middleware('can:viewAny,App\Models\Sensor')->group(function (): void {
            Route::get('sensors', [App\Api\Controllers\LegacyApiController::class, 'list_sensors'])->name('list_sensors');
        });
        Route::middleware('can:viewAny,App\Models\Vlan')->group(function (): void {
            Route::get('vlans', [App\Api\Controllers\LegacyApiController::class, 'list_vlans'])->name('list_vlans');
        });
        Route::middleware('can:viewAny,App\Models\Location')->group(function (): void {
            Route::get('locations', [App\Api\Controllers\LegacyApiController::class, 'list_locations'])->name('list_locations');
        });
    });

    // Inventory
    Route::middleware('can:viewAny,App\Models\Device')->group(function (): void {
        Route::get('inventory/{hostname}', [App\Api\Controllers\LegacyApiController::class, 'get_inventory'])->name('get_inventory');
        Route::get('inventory/{hostname}/all', [App\Api\Controllers\LegacyApiController::class, 'get_inventory_for_device'])->name('get_inventory_for_device');
    });

    // Port Security
    Route::prefix('port_security')->middleware('can:viewAny,App\Models\Port')->group(function (): void {
        Route::get('port/{portid}', [App\Api\Controllers\LegacyApiController::class, 'get_port_security'])->name('get_port_security_by_port');
        Route::get('device/{hostname}', [App\Api\Controllers\LegacyApiController::class, 'get_port_security'])->name('get_port_security_by_hostname');
        Route::get('', [App\Api\Controllers\LegacyApiController::class, 'get_port_security'])->name('get_port_security');
    });

    // Locations
    Route::post('locations', [App\Api\Controllers\LegacyApiController::class, 'add_location'])->name('add_location')->middleware('can:create,App\Models\Location');
    Route::get('location/{location_id_or_name}', [App\Api\Controllers\LegacyApiController::class, 'get_location'])->name('get_location')->middleware('can:viewAny,App\Models\Location');
    Route::patch('locations/{location_id_or_name}', [App\Api\Controllers\LegacyApiController::class, 'edit_location'])->name('edit_location')->middleware('can:update,App\Models\Location');
    Route::delete('locations/{location}', [App\Api\Controllers\LegacyApiController::class, 'del_location'])->name('del_location')->middleware('can:delete,App\Models\Location');
    Route::post('locations/{location}/maintenance', [App\Api\Controllers\LegacyApiController::class, 'maintenance_location'])->name('maintenance_location')->middleware('can:update,App\Models\Location');

    // Route not found
    Route::any('/{path?}', [App\Api\Controllers\LegacyApiController::class, 'api_not_found'])->where('path', '.*');
});
