<?php

use LibreNMS\RRD\RrdDefinition;

$oid_list = ['sgProxyVersion.0', 'sgProxySoftware.0', 'sgProxyHttpClientRequestRate.0', 'sgProxyHttpClientConnections.0', 'sgProxyHttpClientConnectionsActive.0', 'sgProxyHttpClientConnectionsIdle.0', 'sgProxyHttpServerConnections.0', 'sgProxyHttpServerConnectionsActive.0', 'sgProxyHttpServerConnectionsIdle.0'];

$sgos = snmp_get_multi($device, $oid_list, '-OUQs', 'BLUECOAT-SG-PROXY-MIB');

$version = $sgos[0]['sgProxyVersion.0'];
$hardware = $sgos[0]['sgProxySoftware.0'];

if (is_numeric($sgos[0]['sgProxyHttpClientRequestRate'])) {
    $tags = array(
        'rrd_def' => RrdDefinition::make()->addDataset('requests', 'GAUGE', 0),
    );
    $fields = array(
        'requests' => $sgos[0]['sgProxyHttpClientRequestRate'],
    );

    data_update($device, 'sgos_average_requests', $tags, $fields);

    $graphs['sgos_average_requests'] = true;
    echo ' HTTP Req Rate';
}

if (is_numeric($sgos[0]['sgProxyHttpClientConnections'])) {
    $tags = array(
        'rrd_def' => RrdDefinition::make()->addDataset('client_conn', 'GAUGE', 0),
    );
    $fields = array(
        'client_conn' => $sgos[0]['sgProxyHttpClientConnections'],
    );

    data_update($device, 'sgos_client_connections', $tags, $fields);

    $graphs['sgos_client_connections'] = true;
    echo ' Client Conn';
}

if (is_numeric($sgos[0]['sgProxyHttpServerConnections'])) {
    $tags = array(
        'rrd_def' => RrdDefinition::make()->addDataset('server_conn', 'GAUGE', 0),
    );
    $fields = array(
        'server_conn' => $sgos[0]['sgProxyHttpServerConnections'],
    );

    data_update($device, 'sgos_server_connections', $tags, $fields);

    $graphs['sgos_server_connections'] = true;
    echo ' Server Conn';
}

if (is_numeric($sgos[0]['sgProxyHttpClientConnectionsActive'])) {
    $tags = array(
        'rrd_def' => RrdDefinition::make()->addDataset('client_conn_active', 'GAUGE', 0),
    );
    $fields = array(
        'client_conn_active' => $sgos[0]['sgProxyHttpClientConnectionsActive'],
    );

    data_update($device, 'sgos_client_connections_active', $tags, $fields);

    $graphs['sgos_client_connections_active'] = true;
    echo ' Client Conn Active';
}

if (is_numeric($sgos[0]['sgProxyHttpServerConnectionsActive'])) {
    $tags = array(
        'rrd_def' => RrdDefinition::make()->addDataset('server_conn_active', 'GAUGE', 0),
    );
    $fields = array(
        'server_conn_active' => $sgos[0]['sgProxyHttpServerConnectionsActive'],
    );

    data_update($device, 'sgos_server_connections_active', $tags, $fields);

    $graphs['sgos_server_connections_active'] = true;
    echo ' Server Conn Active';
}

if (is_numeric($sgos[0]['sgProxyHttpClientConnectionsIdle'])) {
    $tags = array(
        'rrd_def' => RrdDefinition::make()->addDataset('client_idle', 'GAUGE', 0),
    );
    $fields = array(
        'client_idle' => $sgos[0]['sgProxyHttpClientConnectionsIdle'],
    );

    data_update($device, 'sgos_client_connections_idle', $tags, $fields);

    $graphs['sgos_client_connections_idle'] = true;
    echo ' Client Conne Idle';
}

if (is_numeric($sgos[0]['sgProxyHttpServerConnectionsIdle'])) {
    $tags = array(
        'rrd_def' => RrdDefinition::make()->addDataset('server_idle', 'GAUGE', 0),
    );
    $fields = array(
        'server_idle' => $sgos[0]['sgProxyHttpServerConnectionsIdle'],
    );

    data_update($device, 'sgos_server_connections_idle', $tags, $fields);

    $graphs['sgos_server_connections_idle'] = true;
    echo ' Server Conn Idle';
}
