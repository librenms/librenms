<?php

use LibreNMS\Util\Oid;
use LibreNMS\Util\Rewrite;

$fabosSfpRxPower = SnmpQuery::hideMib()->mibs(['FA-EXT-MIB'])->numericIndex()->walk('FA-EXT-MIB::swSfpRxPower')->valuesByIndex();
$fabosSfpTxPower = SnmpQuery::hideMib()->mibs(['FA-EXT-MIB'])->numericIndex()->walk('FA-EXT-MIB::swSfpTxPower')->valuesByIndex();

$fabosSfpPower = array_merge_recursive($fabosSfpTxPower, $fabosSfpRxPower);

if (! empty($fabosSfpPower)) {
    $ifDescr = SnmpQuery::hideMib()->mibs(['IF-MIB'])->walk('IF-MIB::ifDescr')->table(1) ?? [];
    $ifAdminStatus = SnmpQuery::hideMib()->mibs(['IF-MIB'])->walk('IF-MIB::ifAdminStatus')->table(1) ?? [];
}

foreach ($fabosSfpPower as $fullIndex => $entry) {
    foreach ($entry as $oid => $current) {
        if (is_numeric($current)) {
            $index = array_slice(explode('.', $fullIndex), -1)[0];
            $ifIndex = $index + 1073741823;
            $num_oid = Oid::of($oid . '.' . $fullIndex)->toNumeric('FA-EXT-MIB');
            if ($ifAdminStatus[$ifIndex]['ifAdminStatus'] == '1') {
                discover_sensor(
                    null,
                    'dbm',
                    $device,
                    "$num_oid",
                    "$oid.$index",
                    'brocade',
                    Rewrite::shortenIfName($ifDescr[$ifIndex]['ifDescr']) . ($oid == 'swSfpRxPower' ? ' RX' : ' TX'),
                    1,
                    1,
                    -5,
                    null,
                    null,
                    1,
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
