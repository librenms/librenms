<?php

$oids = snmp_walk($device, 'powerSupplyIndex.1', '-OsqnU', 'IDRAC-MIB-SMIv2');
d_echo($oids . "\n");
$oids = trim($oids);
if ($oids) {
    echo 'Dell iDRAC';
    foreach (explode("\n", $oids) as $data) {
        $data = trim($data);
        if ($data) {
            [$oid,$kind] = explode(' ', $data);
            $split_oid = explode('.', $oid);
            $index = $split_oid[count($split_oid) - 1];
            $voltage_oid = ".1.3.6.1.4.1.674.10892.5.4.600.12.1.16.1.$index";
            $descr_oid = "powerSupplyLocationName.1.$index";
            $limit_oid = "powerSupplyMaximumInputVoltage.1.$index";
            $descr = trim(snmp_get($device, $descr_oid, '-Oqv', 'IDRAC-MIB-SMIv2'), '"');
            $descr = preg_replace('/(Status)/', '', $descr);
            $current = snmp_get($device, $voltage_oid, '-Oqv', 'IDRAC-MIB-SMIv2');
            $high_limit = snmp_get($device, $limit_oid, '-Oqv', 'IDRAC-MIB-SMIv2');
            $divisor = '1';
            discover_sensor($valid['sensor'], 'voltage', $device, $voltage_oid, $index, 'drac', $descr, $divisor, '1', 0, null, null, $high_limit, $current);
        }
    }
}
