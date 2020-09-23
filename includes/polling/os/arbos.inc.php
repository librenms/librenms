<?php

use LibreNMS\RRD\RrdDefinition;

$oids = snmp_get_multi($device, 'deviceTotalFlows.0', '-OQUs', 'PEAKFLOW-SP-MIB');

$flows = $oids[0]['deviceTotalFlows'];

if (is_numeric($flows)) {
    $rrd_def = RrdDefinition::make()->addDataset('flows', 'GAUGE', 0, 3000000);

    $fields = [
        'flows' => $flows,
    ];

    $tags = compact('rrd_def');
    data_update($device, 'arbos_flows', $tags, $fields);

    $os->enableGraph('arbos_flows');
}
