<?php

use LibreNMS\RRD\RrdDefinition;

$hardware = $device['sysDescr'];

$version = explode("ITS", trim(snmp_get($device, '.1.3.6.1.4.1.890.1.15.3.1.6.0', '-Osqv'), '"'), 2);
$version = $version[0];
$serial = trim(snmp_get($device, '.1.3.6.1.4.1.890.1.15.3.1.12.0', '-Oqv'), '"');

$sessions = snmp_get($device, '.1.3.6.1.4.1.890.1.6.22.1.6.0', '-Ovq');
if (is_numeric($sessions)) {
    $rrd_def = RrdDefinition::make()->addDataset('sessions', 'GAUGE', 0, 3000000);
    $fields = array(
        'sessions' => $sessions,
    );
    $tags = compact('rrd_def');
    data_update($device, 'zywall-sessions', $tags, $fields);
    $graphs['zywall_sessions'] = true;
}
