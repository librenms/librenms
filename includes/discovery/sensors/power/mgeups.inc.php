<?php

echo("APC/MGE UPS ");

$oids = trim(snmp_walk($device, ".1.3.6.1.2.1.33.1.3.2.0", "-OsqnU"));
d_echo($oids."\n");
list($unused,$numPhase) = explode(' ', $oids);
for ($i = 1; $i <= $numPhase; $i++) {
    $current_oid   = ".1.3.6.1.2.1.33.1.3.3.1.5.$i";
    $descr      = "Input";
    if ($numPhase > 1) {
        $descr .= " Phase $i";
    }
    $current    = snmp_get($device, $current_oid, "-Oqv");
    $precision  = 1;
    $index      = 100+$i;

    if (is_numeric($current)) {
        discover_sensor($valid['sensor'], 'power', $device, $current_oid, $index, 'upsInputTruePower', $descr, '1', '1', null, null, null, null, $current);
    }
}
