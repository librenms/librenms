<?php

// Same as percent/abb-cms700.inc.php, but the thdUL subtree lives at a
// different OID number on this model ({ insite 37 }, not { cms700 43 }).
$abb_thd_voltage = SnmpQuery::numericIndex()->options(['-OQn'])->walk('.1.3.6.1.4.1.51055.1.37')->values();

foreach ($abb_thd_voltage as $abb_oid => $abb_raw) {
    $abb_phase = (int) substr((string) $abb_oid, strrpos((string) $abb_oid, '.') + 1);
    $abb_descr = "Voltage THD L{$abb_phase}";
    $abb_value = ((float) $abb_raw) / 100;
    discover_sensor(null, 'percent', $device, $abb_oid, 'thdUL.' . $abb_phase, 'abb-insite', $abb_descr, 100, 1, null, null, null, null, $abb_value);
}

unset($abb_thd_voltage, $abb_oid, $abb_raw, $abb_phase, $abb_descr, $abb_value);
