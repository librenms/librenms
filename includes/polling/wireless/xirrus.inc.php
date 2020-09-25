<?php

use LibreNMS\RRD\RrdDefinition;

$associations = [];

// if this config flag is true, don't poll for stations
// this in case of large APs which may have many stations
// to prevent causing long polling times
if (\LibreNMS\Config::get('xirrus_disable_stations') != true) {
    // station associations
    // custom RRDs and graph as each AP may have 16 radios
    $assoc = snmpwalk_cache_oid($device, 'XIRRUS-MIB::stationAssociationIAP', [], 'XIRRUS-MIB');
    foreach ($assoc as $s) {
        $radio = array_pop($s);
        $associations[$radio] = (int) $associations[$radio] + 1;
    }
    unset($radio);
    unset($assoc);
    // write to rrds
    foreach ($associations as $radio => $count) {
        $measurement = 'xirrus_users';
        $rrd_name = [$measurement, $radio];
        $rrd_def = RrdDefinition::make()->addDataset('stations', 'GAUGE', 0, 3200);
        $fields = [
            'stations' => $count,
        ];
        $tags = compact('radio', 'rrd_name', 'rrd_def');
        data_update($device, $measurement, $tags, $fields);
    }
    $os->enableGraph('xirrus_stations');
}

// cleanup
unset($rrd_def, $associations, $tags, $fields, $measurement);
