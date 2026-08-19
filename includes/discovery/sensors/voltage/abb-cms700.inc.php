<?php

// Unlike current, phase voltage (L1-N/L2-N/L3-N) is one value per phase
// for the whole device, not per branch (uL1/uL2/uL3 under { cms700 29 }).
// Same centivolt scale as current's centiamps (raw/100).
$abb_voltages = SnmpQuery::numericIndex()->options(['-OQn'])->walk('.1.3.6.1.4.1.51055.1.29')->values();

foreach ($abb_voltages as $abb_oid => $abb_raw) {
    $abb_phase = (int) substr((string) $abb_oid, strrpos((string) $abb_oid, '.') + 1);
    $abb_descr = "Phase Voltage L{$abb_phase}";
    $abb_value = ((float) $abb_raw) / 100;
    discover_sensor(null, 'voltage', $device, $abb_oid, 'uL.' . $abb_phase, 'abb-cms700', $abb_descr, 100, 1, null, null, null, null, $abb_value);
}

unset($abb_voltages, $abb_oid, $abb_raw, $abb_phase, $abb_descr, $abb_value);
