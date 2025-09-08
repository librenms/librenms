<?php

use LibreNMS\Util\Oid;

$fabosSfpRxPower = SnmpQuery::hideMib()->mibs(['FA-EXT-MIB'])->numericIndex()->walk('FA-EXT-MIB::swSfpRxPower')->valuesByIndex();
$fabosSfpTxPower = SnmpQuery::hideMib()->mibs(['FA-EXT-MIB'])->numericIndex()->walk('FA-EXT-MIB::swSfpTxPower')->valuesByIndex();
$swFCPort = SnmpQuery::hideMib()->mibs(['SW-MIB'])->numericIndex()->walk(['SW-MIB::swFCPortName'])->valuesByIndex();
$fabosSfpPower = array_merge_recursive($fabosSfpTxPower, $fabosSfpRxPower);

foreach ($fabosSfpPower as $fullIndex => $entry) {
    foreach ($entry as $oid => $current) {
        if (is_numeric($current)) {
            $connUnitPortIndex = array_slice(explode('.', $fullIndex), -1)[0];
            $num_oid = Oid::of($oid . '.' . $fullIndex)->toNumeric('FA-EXT-MIB');
            if (! empty($swFCPort[$connUnitPortIndex]['swFCPortName'])) {
                discover_sensor(
                    null,
                    'dbm',
                    $device,
                    "$num_oid",
                    "$oid.$connUnitPortIndex",
                    'brocade',
                    $swFCPort[$connUnitPortIndex]['swFCPortName'] . ($oid == 'swSfpRxPower' ? ' RX' : ' TX'),
                    1,
                    1,
                    -5,
                    null,
                    null,
                    1,
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
