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

Route::group(['prefix' => 'v0', 'namespace' => '\App\Api\Controllers'], function () {
    Route::get('system', 'LegacyApiController@server_info')->name('server_info');
    Route::get(null, 'LegacyApiController@show_endpoints');

    // global read only access required
    Route::middleware(['can:global-read'])->group(function () {
        Route::get('bgp', 'LegacyApiController@list_bgp')->name('list_bgp');
        Route::get('bgp/{id}', 'LegacyApiController@get_bgp')->name('get_bgp');
        Route::get('ospf', 'LegacyApiController@list_ospf')->name('list_ospf');
        Route::get('ospf_ports', 'LegacyApiController@list_ospf_ports')->name('list_ospf_ports');
        Route::get('oxidized/{hostname?}', 'LegacyApiController@list_oxidized')->name('list_oxidized');
        Route::get('devicegroups/{name}', 'LegacyApiController@get_devices_by_group')->name('get_devices_by_group');
        Route::get('devicegroups', 'LegacyApiController@get_device_groups')->name('get_device_groups');
        Route::get('port_groups', 'LegacyApiController@get_port_groups')->name('get_port_groups');
        Route::get('portgroups/multiport/bits/{id}', 'LegacyApiController@get_graph_by_portgroup')->name('get_graph_by_portgroup_multiport_bits');
        Route::get('portgroups/{group}', 'LegacyApiController@get_graph_by_portgroup')->name('get_graph_by_portgroup');
        Route::get('alerts/{id}', 'LegacyApiController@list_alerts')->name('get_alert');
        Route::get('alerts', 'LegacyApiController@list_alerts')->name('list_alerts');
        Route::get('rules/{id}', 'LegacyApiController@list_alert_rules')->name('get_alert_rule');
        Route::get('rules', 'LegacyApiController@list_alert_rules')->name('list_alert_rules');
        Route::get('routing/vrf/{id}', 'LegacyApiController@get_vrf')->name('get_vrf');
        Route::get('routing/ipsec/data/{hostname}', 'LegacyApiController@list_ipsec')->name('list_ipsec');
        Route::get('services', 'LegacyApiController@list_services')->name('list_services');
        Route::get('services/{hostname}', 'LegacyApiController@list_services')->name('list_services_device');

        Route::group(['prefix' => 'resources'], function () {
            Route::get('links/{id}', 'LegacyApiController@get_link')->name('get_link');
            Route::get('locations', 'LegacyApiController@list_locations')->name('list_locations');
            Route::get('ip/addresses', 'LegacyApiController@list_ip_addresses')->name('list_ip_addresses');
            Route::get('ip/arp/{query}/{cidr?}', 'LegacyApiController@list_arp')->name('list_arp');
            Route::get('ip/networks', 'LegacyApiController@list_ip_networks')->name('list_ip_networks');
            Route::get('ip/networks/{id}/ip', 'LegacyApiController@get_network_ip_addresses')->name('get_network_ip_addresses');
        });

        Route::group(['prefix' => 'logs'], function () {
            Route::get('eventlog/{hostname?}', 'LegacyApiController@list_logs')->name('list_eventlog');
            Route::get('syslog/{hostname?}', 'LegacyApiController@list_logs')->name('list_syslog');
            Route::get('alertlog/{hostname?}', 'LegacyApiController@list_logs')->name('list_alertlog');
            Route::get('authlog', 'LegacyApiController@list_logs')->name('list_authlog');
        });
    });

    // admin required
    Route::middleware(['can:admin'])->group(function () {
        Route::group(['prefix' => 'devices'], function () {
            Route::post(null, 'LegacyApiController@add_device')->name('add_device');
            Route::delete('{hostname}', 'LegacyApiController@del_device')->name('del_device');
            Route::patch('{hostname}', 'LegacyApiController@update_device')->name('update_device_field');
            Route::patch('{hostname}/rename/{new_hostname}', 'LegacyApiController@rename_device')->name('rename_device');
            Route::post('{hostname}/components/{type}', 'LegacyApiController@add_components')->name('add_components');
            Route::put('{hostname}/components', 'LegacyApiController@edit_components')->name('edit_components');
            Route::delete('{hostname}/components/{component}', 'LegacyApiController@delete_components')->name('delete_components');
        });

        Route::post('bills', 'LegacyApiController@create_edit_bill')->name('create_bill');
        Route::delete('bills/{bill_id}', 'LegacyApiController@delete_bill')->name('delete_bill');
        Route::put('alerts/{id}', 'LegacyApiController@ack_alert')->name('ack_alert');
        Route::put('alerts/unmute/{id}', 'LegacyApiController@unmute_alert')->name('unmute_alert');
        Route::post('rules', 'LegacyApiController@add_edit_rule')->name('add_rule');
        Route::put('rules', 'LegacyApiController@add_edit_rule')->name('edit_rule');
        Route::delete('rules/{id}', 'LegacyApiController@delete_rule')->name('delete_rule');
        Route::post('services/{hostname}', 'LegacyApiController@add_service_for_host')->name('add_service_for_host');
        Route::get('oxidized/config/search/{searchstring}', 'LegacyApiController@search_oxidized')->name('search_oxidized');
        Route::get('oxidized/config/{device_name}', 'LegacyApiController@get_oxidized_config')->name('get_oxidized_config');
        Route::post('devicegroups', 'LegacyApiController@add_device_group')->name('add_device_group');
        Route::post('port_groups', 'LegacyApiController@add_port_group')->name('add_port_group');
        Route::post('devices/{id}/parents', 'LegacyApiController@add_parents_to_host')->name('add_parents_to_host');
        Route::delete('/devices/{id}/parents', 'LegacyApiController@del_parents_from_host')->name('del_parents_from_host');
        Route::post('locations', 'LegacyApiController@add_location')->name('add_location');
        Route::patch('locations/{location_id_or_name}', 'LegacyApiController@edit_location')->name('edit_location');
        Route::delete('locations/{location}', 'LegacyApiController@del_location')->name('del_location');
        Route::delete('services/{id}', 'LegacyApiController@del_service_from_host')->name('del_service_from_host');
        Route::patch('services/{id}', 'LegacyApiController@edit_service_for_host')->name('edit_service_for_host');
    });

    // restricted by access
    Route::group(['prefix' => 'devices'], function () {
        Route::get('{hostname}', 'LegacyApiController@get_device')->name('get_device');
        Route::get('{hostname}/discover', 'LegacyApiController@trigger_device_discovery')->name('trigger_device_discovery');
        Route::get('{hostname}/availability', 'LegacyApiController@device_availability')->name('device_availability');
        Route::get('{hostname}/outages', 'LegacyApiController@device_outages')->name('device_outages');
        Route::get('{hostname}/graphs/health/{type}/{sensor_id?}', 'LegacyApiController@get_graph_generic_by_hostname')->name('get_health_graph');
        Route::get('{hostname}/graphs/wireless/{type}/{sensor_id?}', 'LegacyApiController@get_graph_generic_by_hostname')->name('get_wireless_graph');
        Route::get('{hostname}/vlans', 'LegacyApiController@get_vlans')->name('get_vlans');
        Route::get('{hostname}/links', 'LegacyApiController@list_links')->name('list_links_device');
        Route::get('{hostname}/graphs', 'LegacyApiController@get_graphs')->name('get_graphs');
        Route::get('{hostname}/fdb', 'LegacyApiController@get_fdb')->name('get_fdb');
        Route::get('{hostname}/health/{type?}/{sensor_id?}', 'LegacyApiController@list_available_health_graphs')->name('list_available_health_graphs');
        Route::get('{hostname}/wireless/{type?}/{sensor_id?}', 'LegacyApiController@list_available_wireless_graphs')->name('list_available_wireless_graphs');
        Route::get('{hostname}/ports', 'LegacyApiController@get_port_graphs')->name('get_port_graphs');
        Route::get('{hostname}/ip', 'LegacyApiController@get_device_ip_addresses')->name('get_ip_addresses');
        Route::get('{hostname}/port_stack', 'LegacyApiController@get_port_stack')->name('get_port_stack');
        Route::get('{hostname}/components', 'LegacyApiController@get_components')->name('get_components');
        Route::get('{hostname}/groups', 'LegacyApiController@get_device_groups')->name('get_device_groups_device');
        // consumes the route below, but passes to it when detected
        Route::get('{hostname}/ports/{ifname}', 'LegacyApiController@get_port_stats_by_port_hostname')->name('get_port_stats_by_port_hostname')->where('ifname', '.*');
        Route::get('{hostname}/ports/{ifname}/{type}', 'LegacyApiController@get_graph_by_port_hostname')->name('get_graph_by_port_hostname');

        Route::get('{hostname}/{type}', 'LegacyApiController@get_graph_generic_by_hostname')->name('get_graph_generic_by_hostname');
        Route::get(null, 'LegacyApiController@list_devices')->name('list_devices');
    });

    Route::group(['prefix' => 'ports'], function () {
        Route::get('{portid}', 'LegacyApiController@get_port_info')->name('get_port_info');
        Route::get('{portid}/ip', 'LegacyApiController@get_port_ip_addresses')->name('get_port_ip_info');
        Route::get('search/{search}', 'LegacyApiController@search_ports')->name('search_ports');
        Route::get(null, 'LegacyApiController@get_all_ports')->name('get_all_ports');
    });

    Route::group(['prefix' => 'bills'], function () {
        Route::get(null, 'LegacyApiController@list_bills')->name('list_bills');
        Route::get('{bill_id}', 'LegacyApiController@list_bills')->name('get_bill');
        Route::get('{bill_id}/graphs/{graph_type}', 'LegacyApiController@get_bill_graph')->name('get_bill_graph');
        Route::get('{bill_id}/graphdata/{graph_type}', 'LegacyApiController@get_bill_graphdata')->name('get_bill_graphdata');
        Route::get('{bill_id}/history', 'LegacyApiController@get_bill_history')->name('get_bill_history');
        Route::get('{bill_id}/history/{bill_hist_id}/graphs/{graph_type}', 'LegacyApiController@get_bill_history_graph')->name('get_bill_history_graph');
        Route::get('{bill_id}/history/{bill_hist_id}/graphdata/{graph_type}', 'LegacyApiController@get_bill_history_graphdata')->name('get_bill_history_graphdata');
    });

    Route::group(['prefix' => 'routing'], function () {
        Route::get('bgp/cbgp', 'LegacyApiController@list_cbgp')->name('list_cbgp');
        Route::get('vrf', 'LegacyApiController@list_vrf')->name('list_vrf');
    });

    Route::group(['prefix' => 'resources'], function () {
        Route::get('fdb', 'LegacyApiController@list_fdb')->name('list_fdb');
        Route::get('fdb/{mac}', 'LegacyApiController@list_fdb')->name('list_fdb_mac');
        Route::get('links', 'LegacyApiController@list_links')->name('list_links');
        Route::get('sensors', 'LegacyApiController@list_sensors')->name('list_sensors');
        Route::get('vlans', 'LegacyApiController@list_vlans')->name('list_vlans');
    });

    Route::get('inventory/{hostname}', 'LegacyApiController@get_inventory')->name('get_inventory');
    Route::get('inventory/{hostname}/all', 'LegacyApiController@get_inventory_for_device')->name('get_inventory_for_device');

    // Route not found
    Route::any('/{path?}', 'LegacyApiController@api_not_found')->where('path', '.*');
});
