<?php

/*
 * LibreNMS
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 *
 * @link       https://www.librenms.org
*/

$walk = snmpwalk_cache_oid($device, 'prtMarkerTable', [], 'Printer-MIB');

foreach ($walk as $index => $data) {
    discover_sensor(
        null,
        'count',
        $device,
        '.1.3.6.1.2.1.43.10.2.1.4.' . $index, // Printer-MIB::prtMarkerLifeCount.1.1
        'prtMarkerLifeCount',
        $device['os'],
        'Life time ' . $data['prtMarkerCounterUnit'],
        1,
        1,
        null,
        null,
        null,
        null,
        $data['prtMarkerLifeCount'],
    );

    discover_sensor(
        null,
        'count',
        $device,
        '.1.3.6.1.2.1.43.10.2.1.5.' . $index, // Printer-MIB::prtMarkerPowerOnCount.1.1
        'prtMarkerPowerOnCount',
        $device['os'],
        ucfirst($data['prtMarkerCounterUnit']) . ' since powered on',
        1,
        1,
        null,
        null,
        null,
        null,
        $data['prtMarkerPowerOnCount'],
    );

    break; // only discover the first ones, others mostly duplicate
}

if ($device['os'] == 'konica') {
    $oids = [
        '.1.3.6.1.4.1.18334.1.1.1.5.7.2.1.3.0' => 'Total print duplex',
        '.1.3.6.1.4.1.18334.1.1.1.5.7.2.1.5.0' => 'Total scans',
        '.1.3.6.1.4.1.18334.1.1.1.5.7.2.2.1.5.1.1' => 'Total copy black',
        '.1.3.6.1.4.1.18334.1.1.1.5.7.2.2.1.5.2.1' => 'Total copy color',
        '.1.3.6.1.4.1.18334.1.1.1.5.7.2.3.1.7.1' => 'Total print black',
        '.1.3.6.1.4.1.18334.1.1.1.5.7.2.2.1.5.1.2' => 'Total print black',
        '.1.3.6.1.4.1.18334.1.1.1.5.7.2.3.1.11.1' => 'Total print color',
        '.1.3.6.1.4.1.18334.1.1.1.5.7.2.2.1.5.2.2' => 'Total print color',
    ];
    foreach ($oids as $oid => $cntName) {
        $value = intval(\SnmpQuery::get($oid)->value());
        if ($value > 0) {
            $oidArray = explode('.', $oid);
            $maxKey = max(array_keys($oidArray));
            $index = str_replace(' ', '', ucwords($cntName)) . '.' . $oidArray[$maxKey - 1] . '.' . $oidArray[$maxKey];
            discover_sensor(null, 'count', $device, $oid, $index, $device['os'], $cntName, 1, 1, null, null, null, null, $value, 'snmp', null, null, null, 'Konica MIB');
        }
    }
}

if ($device['os'] == 'sharp') {
    $oids = SnmpQuery::walk('SNMPv2-SMI::enterprises.2385.1.1.19.2.1')->table();
    $valuesData = $oids['SNMPv2-SMI::enterprises'][2385][1][1][19][2][1][3];
    $namesData = $oids['SNMPv2-SMI::enterprises'][2385][1][1][19][2][1][4];
    foreach ($namesData as $index1 => $nData1) {
        foreach ($nData1 as $index2 => $nData2) {
            foreach ($nData2 as $index3 => $sensorName) {
                $value = $valuesData[$index1][$index2][$index3];
                $oid = '.1.3.6.1.4.1.2385.1.1.19.2.1.3.' . $index1 . '.' . $index2 . '.' . $index3;
                $index = $sensorName . '.' . $index3;
                discover_sensor(null, 'count', $device, $oid, $index, $device['os'], $sensorName, 1, 1, null, null, null, null, $value, 'snmp', null, null, null, 'Sharp MIB');
            }
        }
    }
}
