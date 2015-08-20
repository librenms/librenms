<?php

// RFC1628 UPS
if (isset($config['modules_compat']['rfc1628'][$device['os']]) && $config['modules_compat']['rfc1628'][$device['os']]) {
    echo("RFC1628 ");

    $oids = trim(snmp_walk($device, "1.3.6.1.2.1.33.1.4.3.0", "-OsqnU"));
    d_echo($oids."\n");
    list($unused,$numPhase) = explode(' ',$oids);
    for($i = 1; $i <= $numPhase;$i++) {
        $current_oid   = ".1.3.6.1.2.1.33.1.4.4.1.4.$i";
        $descr      = "Output";
        if ($numPhase > 1) $descr .= " Phase $i";
        $current    = snmp_get($device, $current_oid, "-Oqv");
        $type       = "rfc1628";
        $precision  = 1;
        $index      = 300+$i;

        discover_sensor($valid['sensor'], 'power', $device, $current_oid, $index, $type, $descr, '1', '1', NULL, NULL, NULL, NULL, $current);
    }

    $oids = trim(snmp_walk($device, "1.3.6.1.2.1.33.1.3.2.0", "-OsqnU"));
    d_echo($oids."\n");
    list($unused,$numPhase) = explode(' ',$oids);
    for($i = 1; $i <= $numPhase;$i++) {
        $current_oid   = "1.3.6.1.2.1.33.1.3.3.1.5.$i";
        $descr      = "Input";
        if ($numPhase > 1) $descr .= " Phase $i";
        $current    = snmp_get($device, $current_oid, "-Oqv");
        $type       = "rfc1628";
        $precision  = 1;
        $index      = 100+$i;

        discover_sensor($valid['sensor'], 'power', $device, $current_oid, $index, $type, $descr, '1', '1', NULL, NULL, NULL, NULL, $current);
    }

    $oids = trim(snmp_walk($device, "1.3.6.1.2.1.33.1.5.2.0", "-OsqnU"));
    d_echo($oids."\n");
    list($unused,$numPhase) = explode(' ',$oids);
    for($i = 1; $i <= $numPhase;$i++) {
        $current_oid   = ".1.3.6.1.2.1.33.1.5.3.1.4.$i";
        $descr      = "Bypass";
        if ($numPhase > 1) $descr .= " Phase $i";
        $current    = snmp_get($device, $current_oid, "-Oqv");
        $type       = "rfc1628";
        $precision  = 1;
        $index      = 200+$i;

        discover_sensor($valid['sensor'], 'power', $device, $current_oid, $index, $type, $descr, '1', '1', NULL, NULL, NULL, NULL, $current);
    }
}
