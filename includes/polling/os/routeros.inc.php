<?php

use LibreNMS\RRD\RrdDefinition;

$version = trim(snmp_get($device, '1.3.6.1.4.1.14988.1.1.4.4.0', '-OQv', '', ''), '"');
if (strstr($device['sysDescr'], 'RouterOS')) {
    $hardware = substr($device['sysDescr'], 9);
}

$features = 'Level '.trim(snmp_get($device, '1.3.6.1.4.1.14988.1.1.4.3.0', '-OQv', '', ''), '"');
$serial = trim(snmp_get($device, '1.3.6.1.4.1.14988.1.1.7.3.0', '-OQv', '', ''), '"');


$leases = snmp_get($device, 'mtxrDHCPLeaseCount.0', '-OQv', 'MIKROTIK-MIB');

if (is_numeric($leases)) {
    $rrd_def = RrdDefinition::make()->addDataset('leases', 'GAUGE', 0);

    $fields = array(
        'leases' => $leases,
    );

    $tags = compact('rrd_def');
    data_update($device, 'routeros_leases', $tags, $fields);
    $graphs['routeros_leases'] = true;
}

unset($leases);

$pppoe_sessions = snmp_get($device, '1.3.6.1.4.1.9.9.150.1.1.1.0', '-OQv', '', '');

if (is_numeric($pppoe_sessions)) {
    $rrd_def = RrdDefinition::make()->addDataset('pppoe_sessions', 'GAUGE', 0);

    $fields = array(
        'pppoe_sessions' => $pppoe_sessions,
    );

    $tags = compact('rrd_def');
    data_update($device, 'routeros_pppoe_sessions', $tags, $fields);
    $graphs['routeros_pppoe_sessions'] = true;
}

unset($pppoe_sessions);
