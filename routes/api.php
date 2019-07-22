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
        Route::get('oxidized/{hostname?}', 'LegacyApiController@list_oxidized')->name('list_oxidized');
        Route::get('devicegroups/{name}', 'LegacyApiController@get_devices_by_group')->name('get_devices_by_group');
        Route::get('devicegroups', 'LegacyApiController@get_device_groups')->name('get_device_groups');
        Route::get('portgroups/multiport/bits/{id}', 'LegacyApiController@get_graph_by_portgroup')->name('get_graph_by_portgroup_multiport_bits');
        Route::get('portgroups/{group}', 'LegacyApiController@get_graph_by_portgroup')->name('get_graph_by_portgroup');

        Route::get('resources/ip/networks/{id}/ip', 'LegacyApiController@get_network_ip_addresses')->name('get_network_ip_addresses');
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
        Route::group(['prefix' => 'bills'], function () {
            Route::post(null, 'LegacyApiController@create_edit_bill')->name('create_bill');
            Route::delete('{bill_id}', 'LegacyApiController@delete_bill')->name('delete_bill');
        });
    });

    // restricted by access
    Route::group(['prefix' => 'devices'], function () {
        Route::get('{hostname}', 'LegacyApiController@get_device')->name('get_device');
        Route::get('{hostname}/graphs/health/{type}/{sensor_id?}', 'LegacyApiController@get_graph_generic_by_hostname')->name('get_health_graph');
        Route::get('{hostname}/graphs/wireless/{type}/{sensor_id?}', 'LegacyApiController@get_graph_generic_by_hostname')->name('get_wireless_graph');
        Route::get('{hostname}/vlans', 'LegacyApiController@get_vlans')->name('get_vlans');
        Route::get('{hostname}/links', 'LegacyApiController@list_links')->name('list_links');
        Route::get('{hostname}/graphs', 'LegacyApiController@get_graphs')->name('get_graphs');
        Route::get('{hostname}/fdb', 'LegacyApiController@get_fdb')->name('get_fdb');
        Route::get('{hostname}/health/{type?}/{sensor_id?}', 'LegacyApiController@list_available_health_graphs')->name('list_available_health_graphs');
        Route::get('{hostname}/wireless/{type?}/{sensor_id?}', 'LegacyApiController@list_available_wireless_graphs')->name('list_available_wireless_graphs');
        Route::get('{hostname}/ports', 'LegacyApiController@get_port_graphs')->name('get_port_graphs');
        Route::get('{hostname}/ip', 'LegacyApiController@get_device_ip_addresses')->name('get_ip_addresses');
        Route::get('{hostname}/port_stack', 'LegacyApiController@get_port_stack')->name('get_port_stack');
        Route::get('{hostname}/components', 'LegacyApiController@get_components')->name('get_components');
        Route::get('{hostname}/groups', 'LegacyApiController@get_device_groups')->name('get_device_groups');
        Route::get('{hostname}/ports/{ifname}', 'LegacyApiController@get_port_stats_by_port_hostname')->name('get_port_stats_by_port_hostname');
        Route::get('{hostname}/ports/{ifname}/{type}', 'LegacyApiController@get_graph_by_port_hostname')->name('get_graph_by_port_hostname');

        Route::get('{hostname}/{type}', 'LegacyApiController@get_graph_generic_by_hostname')->name('get_graph_generic_by_hostname');
        Route::get(null, 'LegacyApiController@list_devices')->name('list_devices');
    });

    Route::group(['prefix' => 'ports'], function () {
        Route::get('{portid}', 'LegacyApiController@get_port_info')->name('get_port_info');
        Route::get('{portid}/ip', 'LegacyApiController@get_port_ip_addresses')->name('get_port_ip_info');
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

    // Route not found
    Route::any('/{path?}', 'LegacyApiController@api_not_found')->where('path', '.*');
});
