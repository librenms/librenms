<?php
$version = trim(snmp_get($device, '.1.3.6.1.4.1.3375.2.1.4.2.0', '-OQv'), '"');
$hardware = trim(snmp_get($device, '.1.3.6.1.4.1.3375.2.1.3.5.2.0', '-OQv'), '"');
$serial = trim(snmp_get($device, '.1.3.6.1.4.1.3375.2.1.3.3.3.0', '-OQv'), '"');

use LibreNMS\RRD\RrdDefinition;

$sessions = snmp_get($device, 'apmAccessStatCurrentActiveSessions.0', '-OQv', 'F5-BIGIP-APM-MIB');

if (is_numeric($sessions)) {
    $rrd_def = RrdDefinition::make()->addDataset('sessions', 'GAUGE', 0);

    $fields = array(
        'sessions' => $sessions,
    );

    $tags = compact('rrd_def');
    data_update($device, 'bigip_apm_sessions', $tags, $fields);
    $graphs['bigip_apm_sessions'] = true;
}



$sysClientsslStatTotNativeConns = snmp_get($device, 'sysClientsslStatTotNativeConns.0', '-OQv', 'F5-BIGIP-SYSTEM-MIB');
$sysClientsslStatTotCompatConns = snmp_get($device, 'sysClientsslStatTotCompatConns.0', '-OQv', 'F5-BIGIP-SYSTEM-MIB');
if (is_numeric($sysClientsslStatTotNativeConns) && is_numeric($sysClientsslStatTotCompatConns)) {
    $rrd_def = RrdDefinition::make()
        ->addDataset('TotNativeConns', 'COUNTER', 0)
        ->addDataset('TotCompatConns', 'COUNTER', 0);
    $fields = array(
            'TotNativeConns' => $sysClientsslStatTotNativeConns,
            'TotCompatConns' => $sysClientsslStatTotCompatConns,
    );
    $tags = compact('rrd_def');
    data_update($device, 'bigip_system_tps', $tags, $fields);
    $graphs['bigip_system_tps'] = true;
}



$sysStatClientTotConns = snmp_get($device, 'sysStatClientTotConns.0', '-OQv', 'F5-BIGIP-SYSTEM-MIB');
if (is_numeric($sysStatClientTotConns)) {
    $rrd_def = RrdDefinition::make()->addDataset('ClientTotConns', 'COUNTER', 0);
    $fields = array(
            'ClientTotConns' => $sysStatClientTotConns,
    );
    $tags = compact('rrd_def');
    data_update($device, 'bigip_system_client_connections', $tags, $fields);
    $graphs['bigip_system_client_connection_rate'] = true;
}

$sysStatServerTotConns = snmp_get($device, 'sysStatServerTotConns.0', '-OQv', 'F5-BIGIP-SYSTEM-MIB');
if (is_numeric($sysStatServerTotConns)) {
    $rrd_def = RrdDefinition::make()->addDataset('ServerTotConns', 'COUNTER', 0);
    $fields = array(
            'ServerTotConns' => $sysStatServerTotConns,
    );
    $tags = compact('rrd_def');
    data_update($device, 'bigip_system_server_connections', $tags, $fields);
    $graphs['bigip_system_server_connection_rate'] = true;
}

$sysStatClientCurConns = snmp_get($device, 'sysStatClientCurConns.0', '-OQv', 'F5-BIGIP-SYSTEM-MIB');
if (is_numeric($sysStatClientCurConns)) {
    $rrd_def = RrdDefinition::make()->addDataset('ClientCurConns', 'GAUGE', 0);
    $fields = array(
            'ClientCurConns' => $sysStatClientCurConns,
    );
    $tags = compact('rrd_def');
    data_update($device, 'bigip_system_client_concurrent_connections', $tags, $fields);
    $graphs['bigip_system_client_concurrent_connections'] = true;
}

$sysStatServerCurConns = snmp_get($device, 'sysStatServerCurConns.0', '-OQv', 'F5-BIGIP-SYSTEM-MIB');
if (is_numeric($sysStatServerTotConns)) {
    $rrd_def = RrdDefinition::make()->addDataset('ServerCurConns', 'GAUGE', 0);
    $fields = array(
            'ServerCurConns' => $sysStatServerCurConns,
    );
    $tags = compact('rrd_def');
    data_update($device, 'bigip_system_server_concurrent_connections', $tags, $fields);
    $graphs['bigip_system_server_concurrent_connections'] = true;
}
