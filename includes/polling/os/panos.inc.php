<?php

use LibreNMS\RRD\RrdDefinition;

$hardware = trim(snmp_get($device, '1.3.6.1.4.1.25461.2.1.2.2.1.0', '-OQv', '', ''), '" ');
$version  = trim(snmp_get($device, '1.3.6.1.4.1.25461.2.1.2.1.1.0', '-OQv', '', ''), '" ');
$serial   = trim(snmp_get($device, '1.3.6.1.4.1.25461.2.1.2.1.3.0', '-OQv', '', ''), '" ');

// list(,,,$hardware) = explode (" ", $poll_device['sysDescr']);
$sessions = snmp_get($device, '1.3.6.1.4.1.25461.2.1.2.3.3.0', '-Ovq');

if (is_numeric($sessions)) {
    $rrd_def = RrdDefinition::make()->addDataset('sessions', 'GAUGE', 0, 3000000);

    $fields = array(
        'sessions' => $sessions,
    );

    $tags = compact('rrd_def');
    data_update($device, 'panos-sessions', $tags, $fields);

    $graphs['panos_sessions'] = true;
}

$sessions_tcp = snmp_get($device, '1.3.6.1.4.1.25461.2.1.2.3.4.0', '-Ovq');

if (is_numeric($sessions_tcp)) {
    $rrd_def = RrdDefinition::make()->addDataset('sessions_tcp', 'GAUGE', 0, 3000000);

    $fields = array(
        'sessions_tcp' => $sessions_tcp,
    );

    $tags = compact('rrd_def');
    data_update($device, 'panos-sessions-tcp', $tags, $fields);

    $graphs['panos_sessions_tcp'] = true;
}

$sessions_udp = snmp_get($device, '1.3.6.1.4.1.25461.2.1.2.3.5.0', '-Ovq');

if (is_numeric($sessions_udp)) {
    $rrd_def = RrdDefinition::make()->addDataset('sessions_udp', 'GAUGE', 0, 3000000);

    $fields = array(
        'sessions_udp' => $sessions_udp,
    );

    $tags = compact('rrd_def');
    data_update($device, 'panos-sessions-udp', $tags, $fields);

    $graphs['panos_sessions_udp'] = true;
}

$sessions_icmp = snmp_get($device, '1.3.6.1.4.1.25461.2.1.2.3.6.0', '-Ovq');

if (is_numeric($sessions_icmp)) {
    $rrd_def = RrdDefinition::make()->addDataset('sessions_icmp', 'GAUGE', 0, 3000000);

    $fields = array(
        'sessions_icmp' => $sessions_icmp,
    );

    $tags = compact('rrd_def');
    data_update($device, 'panos-sessions-icmp', $tags, $fields);

    $graphs['panos_sessions_icmp'] = true;
}

$sessions_ssl = snmp_get($device, '1.3.6.1.4.1.25461.2.1.2.3.7.0', '-Ovq');

if (is_numeric($sessions_ssl)) {
    $rrd_def = RrdDefinition::make()->addDataset('sessions_ssl', 'GAUGE', 0, 3000000);

    $fields = array(
        'sessions_ssl' => $sessions_ssl,
    );

    $tags = compact('rrd_def');
    data_update($device, 'panos-sessions-ssl', $tags, $fields);

    $graphs['panos_sessions_ssl'] = true;
}

$sessions_sslutil = snmp_get($device, '1.3.6.1.4.1.25461.2.1.2.3.8.0', '-Ovq');

if (is_numeric($sessions_sslutil)) {
    $rrd_def = RrdDefinition::make()->addDataset('sessions_sslutil', 'GAUGE', 0, 3000000);

    $fields = array(
        'sessions_sslutil' => $sessions_sslutil,
    );

    $tags = compact('rrd_def');
    data_update($device, 'panos-sessions-sslutil', $tags, $fields);

    $graphs['panos_sessions_sslutil'] = true;
}

$activetunnels = snmp_get($device, '1.3.6.1.4.1.25461.2.1.2.5.1.3.0', '-Ovq');

if (is_numeric($activetunnels)) {
    $rrd_def = RrdDefinition::make()->addDataset('activetunnels', 'GAUGE', 0, 3000000);

    $fields = array(
        'activetunnels' => $activetunnels,
    );

    $tags = compact('rrd_def');
    data_update($device, 'panos-activetunnels', $tags, $fields);

    $graphs['panos_activetunnels'] = true;
}
