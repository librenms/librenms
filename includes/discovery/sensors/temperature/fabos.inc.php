<?php

use LibreNMS\Util\Oid;
use LibreNMS\Util\Rewrite;

$fabosSfpTemp = SnmpQuery::hideMib()->mibs(['FA-EXT-MIB'])->numericIndex()->walk('FA-EXT-MIB::swSfpTemperature')->valuesByIndex();

if (! empty($fabosSfpTemp)) {
    $ifMib = SnmpQuery::hideMib()->mibs(['IF-MIB'])->walk('ifDescr')->table(1);

    foreach ($fabosSfpTemp as $fullIndex => $entry) {
        foreach ($entry as $oid => $current) {
            $num_oid = Oid::of($oid . '.' . $fullIndex)->toNumeric('FA-EXT-MIB');
            $index = array_slice(explode('.', $fullIndex), -1)[0];
            $ifIndex = $index + 1073741823;
            if (is_numeric($current)) {
                discover_sensor(
                    null,
                    'temperature',
                    $device,
                    $num_oid,
                    "$oid.$index",
                    'brocade',
                    Rewrite::shortenIfName($ifMib[$ifIndex]['ifDescr']) . ' Temp',
                    1,
                    1,
                    null,
                    null,
                    null,
                    null,
                    $current,
                    'snmp',
                    $ifIndex,
                    'ports',
                    null,
                    'transceiver'
                );
            }
        }
    }
}
