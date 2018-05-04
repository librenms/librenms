<?php

use LibreNMS\RRD\RrdDefinition;

$version = trim(snmp_get($device, "BLUECOAT-SG-PROXY-MIB::sgProxyVersion.0", "-OQv"), '"');
$hardware = trim(snmp_get($device, "BLUECOAT-SG-PROXY-MIB::sgProxySoftware.0", "-OQv"), '"');
$hostname = trim(snmp_get($device, "SNMPv2-MIB::sysName.0", "-OQv"), '"');
$sgos_requests = snmp_get($device, "BLUECOAT-SG-PROXY-MIB::sgProxyHttpClientRequestRate.0", "-OQvU");
$sgos_client_conn = snmp_get($device, "BLUECOAT-SG-PROXY-MIB::sgProxyHttpClientConnections.0", "-OQvU");
$sgos_server_conn = snmp_get($device, "BLUECOAT-SG-PROXY-MIB::sgProxyHttpServerConnections.0", "-OQvU");
$sgos_client_conn_active = snmp_get($device, "BLUECOAT-SG-PROXY-MIB::sgProxyHttpClientConnectionsActive.0", "-OQvU");
$sgos_client_conn_idle = snmp_get($device, "BLUECOAT-SG-PROXY-MIB::sgProxyHttpClientConnectionsIdle.0", "-OQvU");
$sgos_server_conn_idle = snmp_get($device, "BLUECOAT-SG-PROXY-MIB::sgProxyHttpServerConnectionsIdle.0", "-OQvU");


if (is_numeric($sgos_requests)) {
    $rrd_def = RrdDefinition::make()->addDataset('requests', 'GAUGE', 0);
    $fields = array(
        'requests' => $sgos_requests
    );
    $tags = compact('rrd_def');
    data_update($device, 'sgos_average_requests', $tags, $fields);
    $graphs['sgos_average_requests'] = true;
}

if (is_numeric($sgos_client_conn)) {
    $rrd_def = RrdDefinition::make()->addDataset('client_connections', 'GAUGE', 0);
    $fields = array(
        'client_connections' => $sgos_client_conn
    );
    $tags = compact('rrd_def');
    data_update($device, 'sgos_client_connections', $tags, $fields);
    $graphs['sgos_client_connections'] = true;
}

if (is_numeric($sgos_server_conn)) {
    $rrd_def = RrdDefinition::make()->addDataset('server_connections', 'GAUGE', 0);
    $fields = array(
        'server_connections' => $sgos_server_conn
    );
    $tags = compact('rrd_def');
    data_update($device, 'sgos_server_connections', $tags, $fields);
    $graphs['sgos_server_connections'] = true;
}

if (is_numeric($sgos_client_conn_active)) {
    $rrd_def = RrdDefinition::make()->addDataset('client_conn_active', 'GAUGE', 0);
    $fields = array(
        'client_conn_active' => $sgos_client_conn_active
    );
    $tags = compact('rrd_def');
    data_update($device, 'sgos_client_connections_active', $tags, $fields);
    $graphs['sgos_client_connections_active'] = true;
}

if (is_numeric($sgos_client_conn_idle)) {
    $rrd_def = RrdDefinition::make()->addDataset('client_conn_idle', 'GAUGE', 0);
    $fields = array(
        'client_conn_idle' => $sgos_client_conn_idle
    );
    $tags = compact('rrd_def');
    data_update($device, 'sgos_client_connections_idle', $tags, $fields);
    $graphs['sgos_client_connections_idle'] = true;
}

if (is_numeric($sgos_server_conn_idle)) {
    $rrd_def = RrdDefinition::make()->addDataset('server_conn_idle', 'GAUGE', 0);
    $fields = array(
        'server_conn_idle' => $sgos_server_conn_idle
    );
    $tags = compact('rrd_def');
    data_update($device, 'sgos_server_connections_idle', $tags, $fields);
    $graphs['sgos_server_connections_idle'] = true;
}
