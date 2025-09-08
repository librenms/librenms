<?php

use LibreNMS\Util\Oid;

$fabosSfpTemp = SnmpQuery::hideMib()->mibs(['FA-EXT-MIB'])->numericIndex()->walk('FA-EXT-MIB::swSfpTemperature')->valuesByIndex();
$swFCPort = SnmpQuery::hideMib()->mibs(['SW-MIB'])->numericIndex()->walk('SW-MIB::swFCPortName')->valuesByIndex();

if (! empty($fabosSfpTemp)) {
    foreach ($fabosSfpTemp as $fullIndex => $entry) {
        foreach ($entry as $oid => $current) {
            $connUnitPortIndex = array_slice(explode('.', $fullIndex), -1)[0];
            $num_oid = Oid::of($oid . '.' . $fullIndex)->toNumeric('FA-EXT-MIB');
            if (! empty($swFCPort[$connUnitPortIndex]['swFCPortName'])) {
                discover_sensor(
                    null,
                    'temperature',
                    $device,
                    $num_oid,
                    "$oid.$connUnitPortIndex",
                    'brocade',
                    $swFCPort[$connUnitPortIndex]['swFCPortName'] . ' Temp',
                    1,
                    1,
                    null,
                    null,
                    null,
                    null,
                    $current,
                    'snmp',
                    $connUnitPortIndex,
                    'wFCPortIndex',
                    null,
                    'transceiver'
                );
            }
        }
    }
}
