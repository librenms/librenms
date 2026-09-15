<?php

// Same as voltage/abb-cms700.inc.php, but the uL subtree lives at a
// different OID number on this model ({ insite 24 }, not { cms700 29 }).
$abb_voltages = SnmpQuery::numericIndex()->options(['-OQn'])->walk('.1.3.6.1.4.1.51055.1.24')->values();

foreach ($abb_voltages as $abb_oid => $abb_raw) {
    $abb_phase = (int) substr((string) $abb_oid, strrpos((string) $abb_oid, '.') + 1);
    $abb_descr = "Phase Voltage L{$abb_phase}";
    $abb_value = ((float) $abb_raw) / 100;
    discover_sensor(null, 'voltage', $device, $abb_oid, 'uL.' . $abb_phase, 'abb-insite', $abb_descr, 100, 1, null, null, null, null, $abb_value);
}

unset($abb_voltages, $abb_oid, $abb_raw, $abb_phase, $abb_descr, $abb_value);
