<?php

use LibreNMS\RRD\RrdDefinition;

$router_utilization_snmp = snmpwalk_cache_numerical_oid($device, 'aristaHardwareUtilizationTable', array(), 'ARISTA-HARDWARE-UTILIZATION-MIB');

$uptodate_oids = array();
foreach ($router_utilization_snmp as $values) {
    foreach ($values as $oid => $value) {
        $uptodate_oids[$oid] = $value;
    }
}

foreach ($router_utilization as $entry) {
    // For each router_utilization DB entry, check if the current / maximum value changed to update the DB
    if ($entry['current'] != $uptodate_oids[$entry['oid_current']] or $entry['maximum'] != $uptodate_oids[$entry['oid_maximum']]) {
        dbUpdate(
            array(
                'current' => $uptodate_oids[$entry['oid_current']],
                'maximum' => $uptodate_oids[$entry['oid_maximum']],
            ),
            'router_utilization',
            'id=? AND device_id=?',
            array($entry['id'], $device['device_id'])
        );
    }

    $rrd_name = array('router_utilization', $entry['id'], $entry['resource']);
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
    data_update($device, 'router_utilization', $tags, $fields);

    d_echo("CURRENT = " . $entry['oid_current'] . "\t\t\tVALUE = " . $uptodate_oids[$entry['oid_current']] . PHP_EOL);
    d_echo("MAXIMUM = " . $entry['oid_maximum'] . "\t\t\tVALUE = " . $uptodate_oids[$entry['oid_maximum']] . PHP_EOL);
    d_echo("USE = " . $used . "%" . PHP_EOL . PHP_EOL);
}

unset($uptodate_oids, $router_utilization_snmp, $feature);
