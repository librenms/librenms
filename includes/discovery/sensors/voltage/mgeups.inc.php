<?php

echo 'MGE ';
$oids = trim(snmp_walk($device, 'mgoutputVoltage', '-OsqnU', 'MG-SNMP-UPS-MIB'));
d_echo($oids . "\n");

$numPhase = count(explode("\n", $oids));
for ($i = 1; $i <= $numPhase; $i++) {
    $volt_oid = ".1.3.6.1.4.1.705.1.7.2.1.2.$i";
    $descr = 'Output';
    if ($numPhase > 1) {
        $descr .= " Phase $i";
    }

    $current = snmp_get($device, $volt_oid, '-Oqv');
    if (! $current) {
        $volt_oid .= '.0';
        $current = snmp_get($device, $volt_oid, '-Oqv');
    }

    $current /= 10;
    $type = 'mge-ups';
    $divisor = 10;
    $index = $i;

    discover_sensor($valid['sensor'], 'voltage', $device, $volt_oid, $index, $type, $descr, $divisor, '1', null, null, null, null, $current);
}

$oids = trim(snmp_walk($device, 'mgeinputVoltage', '-OsqnU', 'MG-SNMP-UPS-MIB'));
d_echo($oids . "\n");

$numPhase = count(explode("\n", $oids));
for ($i = 1; $i <= $numPhase; $i++) {
    $volt_oid = ".1.3.6.1.4.1.705.1.6.2.1.2.$i";
    $descr = 'Input';
    if ($numPhase > 1) {
        $descr .= " Phase $i";
    }

    $current = snmp_get($device, $volt_oid, '-Oqv');
    if (! $current) {
        $volt_oid .= '.0';
        $current = snmp_get($device, $volt_oid, '-Oqv');
    }

    $current /= 10;
    $type = 'mge-ups';
    $divisor = 10;
    $index = (100 + $i);

    discover_sensor($valid['sensor'], 'voltage', $device, $volt_oid, $index, $type, $descr, $divisor, '1', null, null, null, null, $current);
}
