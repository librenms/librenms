<?php

// Eaton MGE UPS input/output frequency.
// OIDs and scaling (0.1 Hz) per Eaton MG-SNMP-UPS-MIB
// (upsmgInputFrequency .1.3.6.1.4.1.705.1.6.2.1.3, upsmgOutputFrequency .1.3.6.1.4.1.705.1.7.2.1.3).
$in_freq_oid = '.1.3.6.1.4.1.705.1.6.2.1.3.1.0';
$in_freq = SnmpQuery::get($in_freq_oid)->value();
if (is_numeric($in_freq)) {
    discover_sensor(
        null, 'frequency', $device, $in_freq_oid, 0, 'eaton-mgeups',
        'Input Frequency', 10, 1, 45, 47, 53, 55, $in_freq / 10
    );
}

$out_freq_oid = '.1.3.6.1.4.1.705.1.7.2.1.3.1';
$out_freq = SnmpQuery::get($out_freq_oid)->value();
if (is_numeric($out_freq)) {
    discover_sensor(
        null, 'frequency', $device, $out_freq_oid, 1, 'eaton-mgeups',
        'Output Frequency', 10, 1, 45, 47, 53, 55, $out_freq / 10
    );
}
