<?php

// Supermicro sensors
$oids = snmp_walk($device, '.1.3.6.1.4.1.10876.2.1.1.1.1.3', '-Osqn', 'SUPERMICRO-HEALTH-MIB', 'supermicro');
$oids = trim((string) $oids);
if ($oids) {
    echo 'Supermicro ';
    foreach (explode("\n", $oids) as $data) {
        $data = trim($data);
        if ($data) {
            [$oid, $type] = explode(' ', $data);
            $oid_ex = explode('.', $oid);
            $index = $oid_ex[count($oid_ex) - 1];
            if ($type == 2) {
                $temperature_oid = ".1.3.6.1.4.1.10876.2.1.1.1.1.4.$index";
                $descr_oid = ".1.3.6.1.4.1.10876.2.1.1.1.1.2.$index";
                $limit_oid = ".1.3.6.1.4.1.10876.2.1.1.1.1.5.$index";
                $divisor_oid = ".1.3.6.1.4.1.10876.2.1.1.1.1.9.$index";
                $monitor_oid = ".1.3.6.1.4.1.10876.2.1.1.1.1.10.$index";
                $descr = SnmpQuery::mibDir('supermicro')->get($descr_oid)->value();
                $temperature = SnmpQuery::mibDir('supermicro')->get($temperature_oid)->value();
                $limit = SnmpQuery::mibDir('supermicro')->get($limit_oid)->value();
                $divisor = SnmpQuery::mibDir('supermicro')->get($divisor_oid)->value() || 1;
                $monitor = SnmpQuery::mibDir('supermicro')->get($monitor_oid)->value();
                if ($monitor == 'true') {
                    $descr = trim(str_ireplace('temperature', '', $descr));
                    discover_sensor(null, \LibreNMS\Enum\Sensor::Temperature, $device, $temperature_oid, trim($index, '.'), 'supermicro', $descr, (int) $divisor, '1', null, null, null, $limit, $temperature);
                }
            }
        }
    }
}
