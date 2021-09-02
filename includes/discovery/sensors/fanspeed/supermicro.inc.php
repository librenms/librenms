<?php

$oids = snmp_walk($device, '.1.3.6.1.4.1.10876.2.1.1.1.1.3', '-OsqnU', 'SUPERMICRO-HEALTH-MIB', 'supermicro');
d_echo($oids . "\n");

$oids = trim($oids);
if ($oids) {
    echo 'Supermicro ';
}

foreach (explode("\n", $oids) as $data) {
    $data = trim($data);
    if ($data) {
        [$oid,$kind] = explode(' ', $data);
        $split_oid = explode('.', $oid);
        $index = $split_oid[(count($split_oid) - 1)];
        if ($kind == 0) {
            $fan_oid = ".1.3.6.1.4.1.10876.2.1.1.1.1.4.$index";
            $descr_oid = ".1.3.6.1.4.1.10876.2.1.1.1.1.2.$index";
            $limit_oid = ".1.3.6.1.4.1.10876.2.1.1.1.1.6.$index";
            $divisor_oid = ".1.3.6.1.4.1.10876.2.1.1.1.1.9.$index";
            $monitor_oid = ".1.3.6.1.4.1.10876.2.1.1.1.1.10.$index";
            $descr = snmp_get($device, $descr_oid, '-Oqv', 'SUPERMICRO-HEALTH-MIB', 'supermicro');
            $current = snmp_get($device, $fan_oid, '-Oqv', 'SUPERMICRO-HEALTH-MIB', 'supermicro');
            $low_limit = snmp_get($device, $limit_oid, '-Oqv', 'SUPERMICRO-HEALTH-MIB', 'supermicro');
            // $divisor       = snmp_get($device, $divisor_oid, "-Oqv", "SUPERMICRO-HEALTH-MIB", 'supermicro');
            // ^ This returns an incorrect precision. At least using the raw value... I think. -TL
            $divisor = '1';
            $monitor = snmp_get($device, $monitor_oid, '-Oqv', 'SUPERMICRO-HEALTH-MIB', 'supermicro');
            $descr = str_replace(' Fan Speed', '', $descr);
            $descr = str_replace(' Speed', '', $descr);
            if ($monitor == 'true') {
                discover_sensor($valid['sensor'], 'fanspeed', $device, $fan_oid, $index, 'supermicro', $descr, $divisor, '1', $low_limit, null, null, null, $current);
            }
        }
    }//end if
}//end foreach
