<?php

use LibreNMS\RRD\RrdDefinition;

list($hardware, $version, ) = explode(',', str_replace(', ', ',', $device['sysDescr']));

// Clean up hardware
$hardware = str_replace('PROCURVE', 'ProCurve', $hardware);
if (substr($hardware, 0, 3) == 'HP ') {
    $hardware = substr($hardware, 3);
}

if (substr($hardware, 0, 24) == 'Hewlett-Packard Company ') {
    $hardware = substr($hardware, 24);
}

$altversion = trim(snmp_get($device, 'hpSwitchOsVersion.0', '-Oqv', 'NETSWITCH-MIB'), '"');
if ($altversion) {
    $version = $altversion;
}

$altversion = trim(snmp_get($device, '.1.3.6.1.4.1.11.2.3.7.11.12.1.2.1.11.0', '-Oqv'), '"');
if ($altversion) {
    $version = $altversion;
}

if (preg_match('/^PROCURVE (.*) - (.*)/', $device['sysDescr'], $regexp_result)) {
    $hardware = 'ProCurve '.$regexp_result[1];
    $version  = $regexp_result[2];
}

$serial = snmp_get($device, '.1.3.6.1.4.1.11.2.36.1.1.2.9.0', '-Oqv', 'SEMI-MIB');
$serial = trim(str_replace('"', '', $serial));

// FIXME maybe genericise? or do away with it if we ever walk the full dot1qTpFdbTable as we can count ourselves then ;)
$FdbAddressCount = snmp_get($device, 'hpSwitchFdbAddressCount.0', '-Ovqn', 'STATISTICS-MIB');

if (is_numeric($FdbAddressCount)) {
    $rrd_def = RrdDefinition::make()->addDataset('value', 'GAUGE', -1, 100000);

    $fields = array(
        'value' => $FdbAddressCount,
    );

    $tags = compact('rrd_def');
    data_update($device, 'fdb_count', $tags, $fields);

    $os->enableGraph('fdb_count');

    echo 'FDB Count ';
}
