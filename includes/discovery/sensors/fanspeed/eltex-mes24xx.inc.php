<?php

$tmp_eltex = SnmpQuery::get('ELTEX-MES-ISS-ENV-MIB::eltMesIssEnvFanSpeed')->value();

if ($tmp_eltex) {
        $oid = '.1.3.6.1.4.1.35265.1.139.12.1.5.1.1.1.1';
        $index = 0;
        $type = 'eltex-mes24xx';
        $descr = 'Fan 0';
        $divisor = 1;
        $fanspeed = $tmp_eltex[$oid];
        discover_sensor(null, 'fanspeed', $device, $oid, $index, $type, $descr, $divisor, '1', $min_eltex, null, null, $max_eltex, $fanspeed);
}

if (isset($tmp_eltex['.1.3.6.1.4.1.35265.1.139.12.1.5.1.1.1.2'])) {
        $oid = '.1.3.6.1.4.1.35265.1.139.12.1.5.1.1.1.2';
        $index = 0;
        $type = 'eltex-mes24xx';
        $descr = 'Fan 0';
        $divisor = 1;
        $fanspeed = $tmp_eltex[$oid];
        discover_sensor(null, 'fanspeed', $device, $oid, $index, $type, $descr, $divisor, '1', $min_eltex, null, null, $max_eltex, $fanspeed);
}

unset($tmp_eltex);
