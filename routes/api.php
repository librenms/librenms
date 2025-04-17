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

Route::prefix('v0')->group(function () {
    Route::get('system', [\App\Api\Controllers\LegacyApiController::class, 'server_info'])->name('server_info');
    Route::get('', [\App\Api\Controllers\LegacyApiController::class, 'show_endpoints']);

    // global read only access required
    Route::middleware(['can:global-read'])->group(function () {
        Route::get('bgp', [\App\Api\Controllers\LegacyApiController::class, 'list_bgp'])->name('list_bgp');
        Route::get('bgp/{id}', [\App\Api\Controllers\LegacyApiController::class, 'get_bgp'])->name('get_bgp');
        Route::get('ospf', [\App\Api\Controllers\LegacyApiController::class, 'list_ospf'])->name('list_ospf');
        Route::get('ospf_ports', [\App\Api\Controllers\LegacyApiController::class, 'list_ospf_ports'])->name('list_ospf_ports');
        Route::get('ospfv3', [\App\Api\Controllers\LegacyApiController::class, 'list_ospfv3'])->name('list_ospfv3');
        Route::get('ospfv3_ports', [\App\Api\Controllers\LegacyApiController::class, 'list_ospfv3_ports'])->name('list_ospfv3_ports');
        Route::get('oxidized/{hostname?}', [\App\Api\Controllers\LegacyApiController::class, 'list_oxidized'])->name('list_oxidized');
        Route::get('devicegroups/{name}', [\App\Api\Controllers\LegacyApiController::class, 'get_devices_by_group'])->name('get_devices_by_group');
        Route::get('devicegroups', [\App\Api\Controllers\LegacyApiController::class, 'get_device_groups'])->name('get_device_groups');
        Route::get('port_groups', [\App\Api\Controllers\LegacyApiController::class, 'get_port_groups'])->name('get_port_groups');
        Route::get('port_groups/{name}', [\App\Api\Controllers\LegacyApiController::class, 'get_ports_by_group'])->name('get_ports_by_group');
        Route::get('portgroups/multiport/bits/{id}', [\App\Api\Controllers\LegacyApiController::class, 'get_graph_by_portgroup'])->name('get_graph_by_portgroup_multiport_bits');
        Route::get('portgroups/{group}', [\App\Api\Controllers\LegacyApiController::class, 'get_graph_by_portgroup'])->name('get_graph_by_portgroup');
        Route::get('alerts/{id}', [\App\Api\Controllers\LegacyApiController::class, 'list_alerts'])->name('get_alert');
        Route::get('alerts', [\App\Api\Controllers\LegacyApiController::class, 'list_alerts'])->name('list_alerts');
        Route::get('rules/{id}', [\App\Api\Controllers\LegacyApiController::class, 'list_alert_rules'])->name('get_alert_rule');
        Route::get('rules', [\App\Api\Controllers\LegacyApiController::class, 'list_alert_rules'])->name('list_alert_rules');
        Route::get('routing/vrf/{id}', [\App\Api\Controllers\LegacyApiController::class, 'get_vrf'])->name('get_vrf');
        Route::get('routing/ipsec/data/{hostname}', [\App\Api\Controllers\LegacyApiController::class, 'list_ipsec'])->name('list_ipsec');
        Route::get('services', [\App\Api\Controllers\LegacyApiController::class, 'list_services'])->name('list_services');
        Route::get('services/{hostname}', [\App\Api\Controllers\LegacyApiController::class, 'list_services'])->name('list_services_device');

        Route::prefix('resources')->group(function () {
            Route::get('links/{id}', [\App\Api\Controllers\LegacyApiController::class, 'get_link'])->name('get_link');
            Route::get('locations', [\App\Api\Controllers\LegacyApiController::class, 'list_locations'])->name('list_locations');
            Route::get('ip/addresses/{address_family?}', [\App\Api\Controllers\LegacyApiController::class, 'list_ip_addresses'])->name('list_ip_addresses');
            Route::get('ip/arp/{query}/{cidr?}', [\App\Api\Controllers\LegacyApiController::class, 'list_arp'])->name('list_arp');
            Route::get('ip/networks/{address_family?}', [\App\Api\Controllers\LegacyApiController::class, 'list_ip_networks'])->name('list_ip_networks');
            Route::get('ip/networks/{id}/ip', [\App\Api\Controllers\LegacyApiController::class, 'get_network_ip_addresses'])->name('get_network_ip_addresses');
        });

        Route::prefix('logs')->group(function () {
            Route::get('eventlog/{hostname?}', [\App\Api\Controllers\LegacyApiController::class, 'list_logs'])->name('list_eventlog');
            Route::get('syslog/{hostname?}', [\App\Api\Controllers\LegacyApiController::class, 'list_logs'])->name('list_syslog');
            Route::get('alertlog/{hostname?}', [\App\Api\Controllers\LegacyApiController::class, 'list_logs'])->name('list_alertlog');
            Route::get('authlog', [\App\Api\Controllers\LegacyApiController::class, 'list_logs'])->name('list_authlog');
        });
    });

    // admin required
    Route::middleware(['can:admin'])->group(function () {
        Route::prefix('devices')->group(function () {
            Route::post('', [\App\Api\Controllers\LegacyApiController::class, 'add_device'])->name('add_device');
            Route::delete('{hostname}', [\App\Api\Controllers\LegacyApiController::class, 'del_device'])->name('del_device');
            Route::patch('{hostname}', [\App\Api\Controllers\LegacyApiController::class, 'update_device'])->name('update_device_field');
            Route::patch('{hostname}/rename/{new_hostname}', [\App\Api\Controllers\LegacyApiController::class, 'rename_device'])->name('rename_device');
            Route::post('{hostname}/components/{type}', [\App\Api\Controllers\LegacyApiController::class, 'add_components'])->name('add_components');
            Route::put('{hostname}/components', [\App\Api\Controllers\LegacyApiController::class, 'edit_components'])->name('edit_components');
            Route::delete('{hostname}/components/{component}', [\App\Api\Controllers\LegacyApiController::class, 'delete_components'])->name('delete_components');
            Route::post('{hostname}/maintenance', [\App\Api\Controllers\LegacyApiController::class, 'maintenance_device'])->name('maintenance_device');
        });

        Route::prefix('devicegroups')->group(function () {
            Route::patch('{name}', [\App\Api\Controllers\LegacyApiController::class, 'update_device_group'])->name('update_device_group');
            Route::delete('{name}', [\App\Api\Controllers\LegacyApiController::class, 'delete_device_group'])->name('delete_device_group');
            Route::post('{name}/devices', [\App\Api\Controllers\LegacyApiController::class, 'update_device_group_add_devices'])->name('update_device_group_add_devices');
            Route::delete('{name}/devices', [\App\Api\Controllers\LegacyApiController::class, 'update_device_group_remove_devices'])->name('update_device_group_remove_devices');
            Route::post('{name}/maintenance', [\App\Api\Controllers\LegacyApiController::class, 'maintenance_devicegroup'])->name('maintenance_devicegroup');
        });

        Route::post('bills', [\App\Api\Controllers\LegacyApiController::class, 'create_edit_bill'])->name('create_bill');
        Route::delete('bills/{bill_id}', [\App\Api\Controllers\LegacyApiController::class, 'delete_bill'])->name('delete_bill');
        Route::put('alerts/{id}', [\App\Api\Controllers\LegacyApiController::class, 'ack_alert'])->name('ack_alert');
        Route::put('alerts/unmute/{id}', [\App\Api\Controllers\LegacyApiController::class, 'unmute_alert'])->name('unmute_alert');
        Route::post('rules', [\App\Api\Controllers\LegacyApiController::class, 'add_edit_rule'])->name('add_rule');
        Route::put('rules', [\App\Api\Controllers\LegacyApiController::class, 'add_edit_rule'])->name('edit_rule');
        Route::delete('rules/{id}', [\App\Api\Controllers\LegacyApiController::class, 'delete_rule'])->name('delete_rule');
        Route::post('services/{hostname}', [\App\Api\Controllers\LegacyApiController::class, 'add_service_for_host'])->name('add_service_for_host');
        Route::get('oxidized/config/search/{searchstring}', [\App\Api\Controllers\LegacyApiController::class, 'search_oxidized'])->name('search_oxidized');
        Route::get('oxidized/config/{device_name}', [\App\Api\Controllers\LegacyApiController::class, 'get_oxidized_config'])->name('get_oxidized_config');
        Route::post('devicegroups', [\App\Api\Controllers\LegacyApiController::class, 'add_device_group'])->name('add_device_group');
        Route::patch('devices/{hostname}/port/{portid}', [\App\Api\Controllers\LegacyApiController::class, 'update_device_port_notes'])->name('update_device_port_notes');
        Route::post('port_groups', [\App\Api\Controllers\LegacyApiController::class, 'add_port_group'])->name('add_port_group');
        Route::post('port_groups/{port_group_id}/assign', [\App\Api\Controllers\LegacyApiController::class, 'assign_port_group'])->name('assign_port_group');
        Route::post('port_groups/{port_group_id}/remove', [\App\Api\Controllers\LegacyApiController::class, 'remove_port_group'])->name('remove_port_group');
        Route::post('devices/{id}/parents', [\App\Api\Controllers\LegacyApiController::class, 'add_parents_to_host'])->name('add_parents_to_host');
        Route::delete('/devices/{id}/parents', [\App\Api\Controllers\LegacyApiController::class, 'del_parents_from_host'])->name('del_parents_from_host');
        Route::post('locations', [\App\Api\Controllers\LegacyApiController::class, 'add_location'])->name('add_location');
        Route::get('location/{location_id_or_name}', [\App\Api\Controllers\LegacyApiController::class, 'get_location'])->name('get_location');
        Route::patch('locations/{location_id_or_name}', [\App\Api\Controllers\LegacyApiController::class, 'edit_location'])->name('edit_location');
        Route::delete('locations/{location}', [\App\Api\Controllers\LegacyApiController::class, 'del_location'])->name('del_location');
        Route::delete('services/{id}', [\App\Api\Controllers\LegacyApiController::class, 'del_service_from_host'])->name('del_service_from_host');
        Route::patch('services/{id}', [\App\Api\Controllers\LegacyApiController::class, 'edit_service_for_host'])->name('edit_service_for_host');
        Route::post('bgp/{id}', [\App\Api\Controllers\LegacyApiController::class, 'edit_bgp_descr'])->name('edit_bgp_descr');
        Route::post('syslogsink', [\App\Api\Controllers\LegacyApiController::class, 'post_syslogsink'])->name('post_syslogsink');

        Route::get('poller_group/{poller_group_id_or_name?}', [\App\Api\Controllers\LegacyApiController::class, 'get_poller_group'])->name('get_poller_group');
    });

    // restricted by access
    Route::prefix('devices')->group(function () {
        Route::get('{hostname}', [\App\Api\Controllers\LegacyApiController::class, 'get_device'])->name('get_device');
        Route::get('{hostname}/discover', [\App\Api\Controllers\LegacyApiController::class, 'trigger_device_discovery'])->name('trigger_device_discovery');
        Route::get('{hostname}/availability', [\App\Api\Controllers\LegacyApiController::class, 'device_availability'])->name('device_availability');
        Route::get('{hostname}/outages', [\App\Api\Controllers\LegacyApiController::class, 'device_outages'])->name('device_outages');
        Route::get('{hostname}/graphs/health/{type}/{sensor_id?}', [\App\Api\Controllers\LegacyApiController::class, 'get_graph_generic_by_hostname'])->name('get_health_graph');
        Route::get('{hostname}/graphs/wireless/{type}/{sensor_id?}', [\App\Api\Controllers\LegacyApiController::class, 'get_graph_generic_by_hostname'])->name('get_wireless_graph');
        Route::get('{hostname}/vlans', [\App\Api\Controllers\LegacyApiController::class, 'get_vlans'])->name('get_vlans');
        Route::get('{hostname}/links', [\App\Api\Controllers\LegacyApiController::class, 'list_links'])->name('list_links_device');
        Route::get('{hostname}/graphs', [\App\Api\Controllers\LegacyApiController::class, 'get_graphs'])->name('get_graphs');
        Route::get('{hostname}/fdb', [\App\Api\Controllers\LegacyApiController::class, 'get_fdb'])->name('get_fdb');
        Route::get('{hostname}/health/{type?}/{sensor_id?}', [\App\Api\Controllers\LegacyApiController::class, 'list_available_health_graphs'])->name('list_available_health_graphs');
        Route::get('{hostname}/wireless/{type?}/{sensor_id?}', [\App\Api\Controllers\LegacyApiController::class, 'list_available_wireless_graphs'])->name('list_available_wireless_graphs');
        Route::get('{hostname}/ports', [\App\Api\Controllers\LegacyApiController::class, 'get_port_graphs'])->name('get_port_graphs');
        Route::get('{hostname}/ip', [\App\Api\Controllers\LegacyApiController::class, 'get_device_ip_addresses'])->name('get_ip_addresses');
        Route::get('{hostname}/port_stack', [\App\Api\Controllers\LegacyApiController::class, 'get_port_stack'])->name('get_port_stack');
        Route::get('{hostname}/transceivers', [\App\Api\Controllers\LegacyApiController::class, 'get_transceivers'])->name('get_transceivers');
        Route::get('{hostname}/components', [\App\Api\Controllers\LegacyApiController::class, 'get_components'])->name('get_components');
        Route::get('{hostname}/groups', [\App\Api\Controllers\LegacyApiController::class, 'get_device_groups'])->name('get_device_groups_device');
        Route::get('{hostname}/maintenance', [\App\Api\Controllers\LegacyApiController::class, 'device_under_maintenance'])->name('device_under_maintenance');
        // consumes the route below, but passes to it when detected
        Route::get('{hostname}/ports/{ifname}', [\App\Api\Controllers\LegacyApiController::class, 'get_port_stats_by_port_hostname'])->name('get_port_stats_by_port_hostname')->where('ifname', '.*');
        Route::get('{hostname}/ports/{ifname}/{type}', [\App\Api\Controllers\LegacyApiController::class, 'get_graph_by_port_hostname'])->name('get_graph_by_port_hostname');
        Route::get('{hostname}/services/{id}/graphs/{datasource}', [\App\Api\Controllers\LegacyApiController::class, 'get_graph_by_service'])->name('get_graph_by_service');

        Route::get('{hostname}/{type}', [\App\Api\Controllers\LegacyApiController::class, 'get_graph_generic_by_hostname'])->name('get_graph_generic_by_hostname');
        Route::get('', [\App\Api\Controllers\LegacyApiController::class, 'list_devices'])->name('list_devices');
    });

    Route::prefix('ports')->group(function () {
        Route::get('{portid}', [\App\Api\Controllers\LegacyApiController::class, 'get_port_info'])->name('get_port_info');
        Route::get('{portid}/fdb', [\App\Api\Controllers\LegacyApiController::class, 'get_port_fdb'])->name('get_port_fdb');
        Route::get('{portid}/ip', [\App\Api\Controllers\LegacyApiController::class, 'get_port_ip_addresses'])->name('get_port_ip_info');
        Route::get('{portid}/transceiver', [\App\Api\Controllers\LegacyApiController::class, 'get_port_transceiver'])->name('get_port_transceiver');
        Route::patch('transceiver/metric/{metric}', [\App\Api\Controllers\LegacyApiController::class, 'update_transceiver_metric_thresholds'])->name('update_transceiver_metric_thresholds');
        Route::get('search/{field}/{search?}', [\App\Api\Controllers\LegacyApiController::class, 'search_ports'])->name('search_ports')->where('search', '.*');
        Route::get('mac/{search}', [\App\Api\Controllers\LegacyApiController::class, 'search_by_mac'])->name('search_mac');
        Route::get('', [\App\Api\Controllers\LegacyApiController::class, 'get_all_ports'])->name('get_all_ports');
        Route::get('{portid}/description', [\App\Api\Controllers\LegacyApiController::class, 'get_port_description'])->name('get_port_description');
        Route::patch('{portid}/description', [\App\Api\Controllers\LegacyApiController::class, 'update_port_description'])->name('update_port_description');
    });

    Route::prefix('bills')->group(function () {
        Route::get('', [\App\Api\Controllers\LegacyApiController::class, 'list_bills'])->name('list_bills');
        Route::get('{bill_id}', [\App\Api\Controllers\LegacyApiController::class, 'list_bills'])->name('get_bill');
        Route::get('{bill_id}/graphs/{graph_type}', [\App\Api\Controllers\LegacyApiController::class, 'get_bill_graph'])->name('get_bill_graph');
        Route::get('{bill_id}/graphdata/{graph_type}', [\App\Api\Controllers\LegacyApiController::class, 'get_bill_graphdata'])->name('get_bill_graphdata');
        Route::get('{bill_id}/history', [\App\Api\Controllers\LegacyApiController::class, 'get_bill_history'])->name('get_bill_history');
        Route::get('{bill_id}/history/{bill_hist_id}/graphs/{graph_type}', [\App\Api\Controllers\LegacyApiController::class, 'get_bill_history_graph'])->name('get_bill_history_graph');
        Route::get('{bill_id}/history/{bill_hist_id}/graphdata/{graph_type}', [\App\Api\Controllers\LegacyApiController::class, 'get_bill_history_graphdata'])->name('get_bill_history_graphdata');
    });

    Route::prefix('routing')->group(function () {
        Route::get('bgp/cbgp', [\App\Api\Controllers\LegacyApiController::class, 'list_cbgp'])->name('list_cbgp');
        Route::get('vrf', [\App\Api\Controllers\LegacyApiController::class, 'list_vrf'])->name('list_vrf');
        Route::get('mpls/services', [\App\Api\Controllers\LegacyApiController::class, 'list_mpls_services'])->name('list_mpls_services');
        Route::get('mpls/saps', [\App\Api\Controllers\LegacyApiController::class, 'list_mpls_saps'])->name('list_mpls_saps');
    });

    Route::prefix('resources')->group(function () {
        Route::get('fdb', [\App\Api\Controllers\LegacyApiController::class, 'list_fdb'])->name('list_fdb');
        Route::get('fdb/{mac}', [\App\Api\Controllers\LegacyApiController::class, 'list_fdb'])->name('list_fdb_mac');
        Route::get('fdb/{mac}/detail', [\App\Api\Controllers\LegacyApiController::class, 'list_fdb_detail'])->name('list_fdb_detail');
        Route::get('links', [\App\Api\Controllers\LegacyApiController::class, 'list_links'])->name('list_links');
        Route::get('sensors', [\App\Api\Controllers\LegacyApiController::class, 'list_sensors'])->name('list_sensors');
        Route::get('vlans', [\App\Api\Controllers\LegacyApiController::class, 'list_vlans'])->name('list_vlans');
    });

    Route::get('inventory/{hostname}', [\App\Api\Controllers\LegacyApiController::class, 'get_inventory'])->name('get_inventory');
    Route::get('inventory/{hostname}/all', [\App\Api\Controllers\LegacyApiController::class, 'get_inventory_for_device'])->name('get_inventory_for_device');

    // Route not found
    Route::any('/{path?}', [\App\Api\Controllers\LegacyApiController::class, 'api_not_found'])->where('path', '.*');
});
