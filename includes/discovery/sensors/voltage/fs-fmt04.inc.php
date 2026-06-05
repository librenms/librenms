<?php

/**
 * FS FMT / OAP — EDFA supply voltage (`…10.16.2.1.23`, V×100) when module `.1.0` = on.
 */

echo 'FS FMT EDFA voltage ';

$sensor_type = 'fs-fmt04';
$divisor = 100;

$edfa = '.1.3.6.1.4.1.40989.10.16.2.1';
$edfaState = SnmpQuery::get($edfa . '.1.0')->value();
if (is_numeric($edfaState) && (int) $edfaState === 1) {
    $oid = $edfa . '.23.0';
    $raw = SnmpQuery::get($oid)->value();
    if (is_numeric($raw)) {
        $scaled = (float) $raw / $divisor;
        discover_sensor(
            null,
            'voltage',
            $device,
            $oid,
            'fmt04-edfa-supply-v',
            $sensor_type,
            'EDFA supply voltage',
            $divisor,
            1,
            null,
            null,
            null,
            null,
            $scaled,
            'snmp'
        );
    }
}
