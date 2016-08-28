<?php

if ($device['os'] == 'canopy') {
    $oid  = 'boxTemperatureC.0';
    $oids = trim(str_replace('"', '', snmp_get($device, "$oid", '-OsqnU', 'WHISP-BOX-MIBV2-MIB')));
        d_echo($oids."\n");

    if (!empty($oids)) {
        echo 'Canopy Temperature ';
    }

        $divisor = 1;
        $type    = 'canopy';
    if (!empty($oids)) {
        list(,$current) = explode(' ', $oids);
        $index          = $oid;
        $descr          = 'System Temp';
        discover_sensor($valid['sensor'], 'temperature', $device, $oid, $index, $type, $descr, $divisor, '1', -30, null, null, 50, $current);
    }
}
