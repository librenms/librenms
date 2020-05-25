<?php

use LibreNMS\RRD\RrdDefinition;

$oids = snmp_get_multi($device, ['panChassisType.0', 'panSysSwVersion.0', 'panSysSerialNumber.0', 'panSessionActive.0', 'panSessionActiveTcp.0', 'panSessionActiveUdp.0', 'panSessionActiveICMP.0', 'panSessionActiveSslProxy.0', 'panSessionSslProxyUtilization.0', 'panGPGWUtilizationActiveTunnels.0'], '-OQUs', 'PAN-COMMON-MIB');

$hardware = $oids[0]['panChassisType'];
$version  = $oids[0]['panSysSwVersion'];
$serial   = $oids[0]['panSysSerialNumber'];
$sessions = $oids[0]['panSessionActive'];
$sessions_tcp = $oids[0]['panSessionActiveTcp'];
$sessions_udp = $oids[0]['panSessionActiveUdp'];
$sessions_icmp = $oids[0]['panSessionActiveICMP'];
$sessions_ssl = $oids[0]['panSessionActiveSslProxy'];
$sessions_sslutil = $oids[0]['panSessionSslProxyUtilization'];
$activetunnels = $oids[0]['panGPGWUtilizationActiveTunnels'];

if (is_numeric($sessions)) {
    $rrd_def = RrdDefinition::make()->addDataset('sessions', 'GAUGE', 0, 3000000);

    $fields = array(
        'sessions' => $sessions,
    );

    $tags = compact('rrd_def');
    data_update($device, 'panos-sessions', $tags, $fields);

    $graphs['panos_sessions'] = true;
}

if (is_numeric($sessions_tcp)) {
    $rrd_def = RrdDefinition::make()->addDataset('sessions_tcp', 'GAUGE', 0, 3000000);

    $fields = array(
        'sessions_tcp' => $sessions_tcp,
    );

    $tags = compact('rrd_def');
    data_update($device, 'panos-sessions-tcp', $tags, $fields);

    $graphs['panos_sessions_tcp'] = true;
}

if (is_numeric($sessions_udp)) {
    $rrd_def = RrdDefinition::make()->addDataset('sessions_udp', 'GAUGE', 0, 3000000);

    $fields = array(
        'sessions_udp' => $sessions_udp,
    );

    $tags = compact('rrd_def');
    data_update($device, 'panos-sessions-udp', $tags, $fields);

    $graphs['panos_sessions_udp'] = true;
}

if (is_numeric($sessions_icmp)) {
    $rrd_def = RrdDefinition::make()->addDataset('sessions_icmp', 'GAUGE', 0, 3000000);

    $fields = array(
        'sessions_icmp' => $sessions_icmp,
    );

    $tags = compact('rrd_def');
    data_update($device, 'panos-sessions-icmp', $tags, $fields);

    $graphs['panos_sessions_icmp'] = true;
}

if (is_numeric($sessions_ssl)) {
    $rrd_def = RrdDefinition::make()->addDataset('sessions_ssl', 'GAUGE', 0, 3000000);

    $fields = array(
        'sessions_ssl' => $sessions_ssl,
    );

    $tags = compact('rrd_def');
    data_update($device, 'panos-sessions-ssl', $tags, $fields);

    $graphs['panos_sessions_ssl'] = true;
}

if (is_numeric($sessions_sslutil)) {
    $rrd_def = RrdDefinition::make()->addDataset('sessions_sslutil', 'GAUGE', 0, 3000000);

    $fields = array(
        'sessions_sslutil' => $sessions_sslutil,
    );

    $tags = compact('rrd_def');
    data_update($device, 'panos-sessions-sslutil', $tags, $fields);

    $graphs['panos_sessions_sslutil'] = true;
}

if (is_numeric($activetunnels)) {
    $rrd_def = RrdDefinition::make()->addDataset('activetunnels', 'GAUGE', 0, 3000000);

    $fields = array(
        'activetunnels' => $activetunnels,
    );

    $tags = compact('rrd_def');
    data_update($device, 'panos-activetunnels', $tags, $fields);

    $graphs['panos_activetunnels'] = true;
}
