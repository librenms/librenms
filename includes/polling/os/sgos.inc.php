<?php

use LibreNMS\RRD\RrdDefinition;

$oid_list = ['sgProxyVersion.0', 'sgProxySoftware.0', 'sgProxyHttpClientRequestRate.0', 'sgProxyHttpClientConnections.0', 'sgProxyHttpClientConnectionsActive.0', 'sgProxyHttpClientConnectionsIdle.0', 'sgProxyHttpServerConnections.0', 'sgProxyHttpServerConnectionsActive.0', 'sgProxyHttpServerConnectionsIdle.0'];

$sgos = snmp_get_multi($device, $oid_list, '-OUQs', 'BLUECOAT-SG-PROXY-MIB');

$version = $sgos[0]['sgProxyVersion.0'];
$hardware = $sgos[0]['sgProxySoftware.0'];

if (is_numeric($sgos[0]['sgProxyHttpClientRequestRate'])) {
    $tags = [
        'rrd_def' => RrdDefinition::make()->addDataset('requests', 'GAUGE', 0),
    ];
    $fields = [
        'requests' => $sgos[0]['sgProxyHttpClientRequestRate'],
    ];

    data_update($device, 'sgos_average_requests', $tags, $fields);

    $os->enableGraph('sgos_average_requests');
    echo ' HTTP Req Rate';
}

if (is_numeric($sgos[0]['sgProxyHttpClientConnections'])) {
    $tags = [
        'rrd_def' => RrdDefinition::make()->addDataset('client_conn', 'GAUGE', 0),
    ];
    $fields = [
        'client_conn' => $sgos[0]['sgProxyHttpClientConnections'],
    ];

    data_update($device, 'sgos_client_connections', $tags, $fields);

    $os->enableGraph('sgos_client_connections');
    echo ' Client Conn';
}

if (is_numeric($sgos[0]['sgProxyHttpServerConnections'])) {
    $tags = [
        'rrd_def' => RrdDefinition::make()->addDataset('server_conn', 'GAUGE', 0),
    ];
    $fields = [
        'server_conn' => $sgos[0]['sgProxyHttpServerConnections'],
    ];

    data_update($device, 'sgos_server_connections', $tags, $fields);

    $os->enableGraph('sgos_server_connections');
    echo ' Server Conn';
}

if (is_numeric($sgos[0]['sgProxyHttpClientConnectionsActive'])) {
    $tags = [
        'rrd_def' => RrdDefinition::make()->addDataset('client_conn_active', 'GAUGE', 0),
    ];
    $fields = [
        'client_conn_active' => $sgos[0]['sgProxyHttpClientConnectionsActive'],
    ];

    data_update($device, 'sgos_client_connections_active', $tags, $fields);

    $os->enableGraph('sgos_client_connections_active');
    echo ' Client Conn Active';
}

if (is_numeric($sgos[0]['sgProxyHttpServerConnectionsActive'])) {
    $tags = [
        'rrd_def' => RrdDefinition::make()->addDataset('server_conn_active', 'GAUGE', 0),
    ];
    $fields = [
        'server_conn_active' => $sgos[0]['sgProxyHttpServerConnectionsActive'],
    ];

    data_update($device, 'sgos_server_connections_active', $tags, $fields);

    $os->enableGraph('sgos_server_connections_active');
    echo ' Server Conn Active';
}

if (is_numeric($sgos[0]['sgProxyHttpClientConnectionsIdle'])) {
    $tags = [
        'rrd_def' => RrdDefinition::make()->addDataset('client_idle', 'GAUGE', 0),
    ];
    $fields = [
        'client_idle' => $sgos[0]['sgProxyHttpClientConnectionsIdle'],
    ];

    data_update($device, 'sgos_client_connections_idle', $tags, $fields);

    $os->enableGraph('sgos_client_connections_idle');
    echo ' Client Conne Idle';
}

if (is_numeric($sgos[0]['sgProxyHttpServerConnectionsIdle'])) {
    $tags = [
        'rrd_def' => RrdDefinition::make()->addDataset('server_idle', 'GAUGE', 0),
    ];
    $fields = [
        'server_idle' => $sgos[0]['sgProxyHttpServerConnectionsIdle'],
    ];

    data_update($device, 'sgos_server_connections_idle', $tags, $fields);

    $os->enableGraph('sgos_server_connections_idle');
    echo ' Server Conn Idle';
}
