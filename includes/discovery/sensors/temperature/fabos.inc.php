<?php

use LibreNMS\Util\Rewrite;

$fabosSfpTemp = SnmpQuery::hideMib()->mibs(['FA-EXT-MIB'])->walk('FA-EXT-MIB::swSfpTemperature')->table(1);

if (! empty($fabosSfpTemp)) {
    $ifMib = SnmpQuery::hideMib()->mibs(['IF-MIB'])->walk('ifDescr')->table(1);
    $fabosSfpTemp = reset($fabosSfpTemp);
    d_echo($fabosSfpTemp);
    foreach ($fabosSfpTemp as $oid => $entry) {
        foreach ($entry as $index => $current) {
            if (is_numeric($current)) {
                $ifIndex = $index + 1073741823;
                discover_sensor(
                    null,
                    'temperature',
                    $device,
                    ".$oid.$index",
                    'swSfpTemperature.' . $index,
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
