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
