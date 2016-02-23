<?php

if ($device['os'] == 'gamatronicups') {
    for ($i = 1; $i <= 3; $i++) {
        $current_oid = "GAMATRONIC-MIB::gamatronicLTD.5.4.1.1.3.$i";
        $descr       = "Input Phase $i";
        $current     = snmp_get($device, $current_oid, '-Oqv');
        $type        = 'gamatronicups';
        $precision   = 1;
        $index       = $i;
        $lowlimit    = 0;
        $warnlimit   = null;
        $limit       = null;

        discover_sensor($valid['sensor'], 'current', $device,
            $current_oid, $index, $type,
            $descr, '1', '1', $lowlimit, null, null, null, $current);
    }

    for ($i = 1; $i <= 3; $i++) {
        $current_oid = "GAMATRONIC-MIB::gamatronicLTD.5.5.1.1.3.$i";
        $descr       = "Output Phase $i";
        $current     = snmp_get($device, $current_oid, '-Oqv');
        $type        = 'gamatronicups';
        $precision   = 1;
        $index       = (100 + $i);
        $lowlimit    = 0;
        $warnlimit   = null;
        $limit       = null;

        discover_sensor($valid['sensor'], 'current', $device,
            $current_oid, $index, $type,
            $descr, '1', '1', $lowlimit, null, null, null, $current);
    }
}
