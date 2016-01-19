<?php

if ($device['os'] == 'gamatronicups') {
    for ($i = 1; $i <= 3; $i++) {
        $volt_oid = "GAMATRONIC-MIB::gamatronicLTD.5.4.1.1.2.$i";
        $descr    = "Input Phase $i";
        $volt     = snmp_get($device, $volt_oid, '-Oqv');
        $type     = 'gamatronicups';
        $divisor  = 1;
        $index    = $i;
        $lowlimit = 0;
        $limit    = null;

        discover_sensor($valid['sensor'], 'voltage', $device,
            $volt_oid, $index, $type,
            $descr, $divisor, '1', null, null, null, null, $volt);
    }

    for ($i = 1; $i <= 3; $i++) {
        $volt_oid     = "GAMATRONIC-MIB::gamatronicLTD.5.5.1.1.2.$i";
        $descr    = "Output Phase $i";
        $volt     = snmp_get($device, $volt_oid, '-Oqv');
        $type     = 'gamatronicups';
        $divisor  = 1;
        $index    = (100 + $i);
        $lowlimit = 0;
        $limit    = null;

        discover_sensor($valid['sensor'], 'voltage', $device,
            $volt_oid, $index, $type,
            $descr, $divisor, '1', null, null, null, null, $volt);
    }
}
