<?php

use LibreNMS\RRD\RrdDefinition;

$sessions = snmp_get($device, 'apmAccessStatCurrentActiveSessions.0', '-OQv', 'F5-BIGIP-APM-MIB');

if (is_numeric($sessions)) {
    $rrd_def = RrdDefinition::make()->addDataset('sessions', 'GAUGE', 0);

    $fields = array(
        'sessions' => $sessions,
    );

    $tags = compact('rrd_def');
    data_update($device, 'apm_sessions', $tags, $fields);
    $graphs['apm_sessions'] = true;
}
