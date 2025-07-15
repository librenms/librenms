<?php

$tmp_eltex = snmp_get_multi_oid($device, 'eltMesIssEnvFanTable eltMesIssEnvFanEntry eltMesIssEnvFanSpeed eltMesIssEnvFanSpeedLevel eltMesIssEnvFanThresholdTable eltMesIssEnvFanThresholdEntry eltMesIssEnvFanThresholdLevel eltMesIssEnvFanThresholdMin eltMesIssEnvFanThresholdMax', '-OUQn', 'ELTEX-LTP8X-STANDALONE');

$min_eltex = $tmp_eltex['.1.3.6.1.4.1.35265.1.139.12.1.5.2.1.2.1.1'] ?? null;
$max_eltex = $tmp_eltex['.1.3.6.1.4.1.35265.1.139.12.1.5.2.1.2.1.4'] ?? null;

if (isset($tmp_eltex['.1.3.6.1.4.1.35265.1.139.12.1.5.1.1.1.1'])) {
    if (is_numeric($tmp_eltex['.1.3.6.1.4.1.35265.1.139.12.1.5.1.1.2.1'])) {
        $oid = '.1.3.6.1.4.1.35265.1.139.12.1.5.1.1.2.1';
        $index = 0;
        $type = 'eltex-mes24xx';
        $descr = 'Fan 0';
        $divisor = 1;
        $fanspeed = $tmp_eltex[$oid];
        discover_sensor(null, 'fanspeed', $device, $oid, $index, $type, $descr, $divisor, '1', $min_eltex, null, null, $max_eltex, $fanspeed);
    }
}

if (isset($tmp_eltex['.1.3.6.1.4.1.35265.1.139.12.1.5.1.1.1.2'])) {
    if (is_numeric($tmp_eltex['.1.3.6.1.4.1.35265.1.139.12.1.5.1.1.2.2'])) {
        $oid = '.1.3.6.1.4.1.35265.1.139.12.1.5.1.1.2.2';
        $index = 0;
        $type = 'eltex-mes24xx';
        $descr = 'Fan 0';
        $divisor = 1;
        $fanspeed = $tmp_eltex[$oid];
        discover_sensor(null, 'fanspeed', $device, $oid, $index, $type, $descr, $divisor, '1', $min_eltex, null, null, $max_eltex, $fanspeed);
    }
}

unset($tmp_eltex);
