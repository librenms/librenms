<?php

// Voltage THD, one value per phase for the whole device (thdUL1/thdUL2/
// thdUL3 under { cms700 43 }), same shape as voltage. raw/100 gives
// 2-3% typical values; raw/10 would give an implausible ~20-30%.
$abb_thd_voltage = SnmpQuery::numericIndex()->options(['-OQn'])->walk('.1.3.6.1.4.1.51055.1.43')->values();

foreach ($abb_thd_voltage as $abb_oid => $abb_raw) {
    $abb_phase = (int) substr((string) $abb_oid, strrpos((string) $abb_oid, '.') + 1);
    $abb_descr = "Voltage THD L{$abb_phase}";
    $abb_value = ((float) $abb_raw) / 100;
    discover_sensor(null, 'percent', $device, $abb_oid, 'thdUL.' . $abb_phase, 'abb-cms700', $abb_descr, 100, 1, null, null, null, null, $abb_value);
}

unset($abb_thd_voltage, $abb_oid, $abb_raw, $abb_phase, $abb_descr, $abb_value);
