<?php

use LibreNMS\RRD\RrdDefinition;

$eer = snmp_get($device, 'integerObjects.120.0', '-OQv', 'KELVIN-pCOWeb-Chiller-MIB');

if (is_numeric($eer)) {
    $rrd_def = RrdDefinition::make()->addDataset('eer', 'GAUGE', 0);

    $fields = array(
        'eer' => $eer,
    );

    $tags = compact('rrd_def');
    data_update($device, 'pcoweb-rittalchiller_eer', $tags, $fields);
    $graphs['pcoweb-rittalchiller_eer'] = true;
}
