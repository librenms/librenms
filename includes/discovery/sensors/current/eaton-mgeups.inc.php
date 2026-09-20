<?php

// Eaton MGE UPS output current.
// OID and scaling (0.1 A) per Eaton MG-SNMP-UPS-MIB (upsmgOutputCurrent, .1.3.6.1.4.1.705.1.7.2.1.5).
$out_curr_oid = '.1.3.6.1.4.1.705.1.7.2.1.5.1';
$out_curr = SnmpQuery::get($out_curr_oid)->value();
if (is_numeric($out_curr)) {
    discover_sensor(
        null, 'current', $device, $out_curr_oid, 0, 'eaton-mgeups',
        'Output Current', 10, 1, null, null, null, null, $out_curr / 10
    );
}
