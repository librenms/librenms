<?php

use LibreNMS\RRD\RrdDefinition;

$oids = snmp_get_multi($device, 'pfStateCount.0', '-OQUs', 'OPENBSD-PF-MIB');

$states = $oids[0]['pfStateCount'];

if (is_numeric($states)) {
    $rrd_def = RrdDefinition::make()->addDataset('states', 'GAUGE', 0, 3000000);

    $fields = array(
        'states' => $states,
    );

    $tags = compact('rrd_def');
    data_update($device, 'pf-states', $tags, $fields);

    $graphs['pf_states'] = true;
}
