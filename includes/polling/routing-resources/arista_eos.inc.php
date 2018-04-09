<?php

use LibreNMS\RRD\RrdDefinition;

$routing_resources_snmp = snmpwalk_cache_numerical_oid($device, 'aristaHardwareUtilizationTable', array(), 'ARISTA-HARDWARE-UTILIZATION-MIB');

$uptodate_oids = array();
foreach ($routing_resources_snmp as $values) {
    foreach ($values as $oid => $value) {
        $uptodate_oids[$oid] = $value;
    }
}

foreach ($routing_resources as $entry) {
    // For each routing_resources DB entry, check if the current / maximum value changed to update the DB
    if ($entry['current'] != $uptodate_oids[$entry['oid_current']] or $entry['maximum'] != $uptodate_oids[$entry['oid_maximum']]) {
        dbUpdate(
            array(
                'current' => $uptodate_oids[$entry['oid_current']],
                'maximum' => $uptodate_oids[$entry['oid_maximum']],
            ),
            'routing_resources',
            'id=? AND device_id=?',
            array($entry['id'], $device['device_id'])
        );
    }

    $rrd_name = array('routing_resources', $entry['id'], $entry['resource']);
    $rrd_def = RrdDefinition::make()
        ->addDataset('used', 'GAUGE', 0);
    $used = round($uptodate_oids[$entry['oid_current']] / $uptodate_oids[$entry['oid_maximum']] * 100, 2);
    $fields = array(
        'used' => $used,
    );

    $feature = $entry['feature'];
    if ($entry['feature'] != '' and $entry['forwarding_element'] != '') {
        $label = $entry['feature'] . " - " . $entry['forwarding_element'];
    } elseif ($entry['feature'] != '' and $entry['forwarding_element'] == '') {
        $label = $entry['feature'];
    } elseif ($entry['feature'] == '' and $entry['forwarding_element'] != '') {
        $label = $entry['forwarding_element'];
    } else {
        $label = '';
    }
    $tags = compact('feature', 'label', 'rrd_name', 'rrd_def');
    data_update($device, 'routing_resources', $tags, $fields);

    d_echo("CURRENT = " . $entry['oid_current'] . "\t\t\tVALUE = " . $uptodate_oids[$entry['oid_current']] . PHP_EOL);
    d_echo("MAXIMUM = " . $entry['oid_maximum'] . "\t\t\tVALUE = " . $uptodate_oids[$entry['oid_maximum']] . PHP_EOL);
    d_echo("USE = " . $used . "%" . PHP_EOL . PHP_EOL);
}

unset($uptodate_oids, $routing_resources_snmp, $feature);
