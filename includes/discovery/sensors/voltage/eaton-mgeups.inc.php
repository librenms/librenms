<?php

// Eaton MGE UPS - battery voltage (divisor 10)
$batt_volt_oid = '.1.3.6.1.4.1.705.1.5.5.0';
$batt_volt = SnmpQuery::get($batt_volt_oid)->value();
if (is_numeric($batt_volt)) {
    discover_sensor(
        null, 'voltage', $device, $batt_volt_oid, 'batt', 'eaton-mgeups',
        'Battery', 10, 1, null, null, null, null, $batt_volt / 10
    );
}
