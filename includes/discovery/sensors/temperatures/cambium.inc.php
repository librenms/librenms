<?php

if ($device['os'] == 'cambium') {
    $oid  = 'boxTemperatureC.0';
    $oids = trim(str_replace('"', '', snmp_get($device, "$oid", '-OsqnU', 'WHISP-BOX-MIBV2-MIB')));
        d_echo($oids."\n");

    if (!empty($oids)) {
        echo 'Cambium Temperature ';
    }

        $divisor = 1;
        $type    = 'cambium';
    if (!empty($oids)) {
        list(,$current) = explode(' ', $oids);
        $index          = $oid;
        $descr          = 'System Temp';
        discover_sensor($valid['sensor'], 'temperature', $device, $oid, $index, $type, $descr, $divisor, '1', null, null, null, null, $current);
    }
}
