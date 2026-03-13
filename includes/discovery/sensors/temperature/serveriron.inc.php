<?php

echo ' FOUNDRY-SN-AGENT-MIB';

// Chassis temperature (default)
$high_limit = 110;
$high_warn_limit = 95;

$descr = 'Chassis Temperature';
$oid = '.1.3.6.1.4.1.1991.1.1.1.1.18.0'; // snChasActualTemperature
$warn_oid = '.1.3.6.1.4.1.1991.1.1.1.1.19.0'; // snChasWarningTemperature
$high_oid = '.1.3.6.1.4.1.1991.1.1.1.1.20.0'; // snChasShutdownTemperature
$value = SnmpQuery::get($oid)->value();

$value_warn = SnmpQuery::get($warn_oid)->value();
if (is_numeric($value_warn)) {
    $high_warn_limit = ($value_warn / 2);
}

$value_high = SnmpQuery::get($high_oid)->value();
if (is_numeric($value_high)) {
    $high_limit = ($value_high / 2);
}

if (is_numeric($value)) {
    $current = ($value / 2);
    discover_sensor(null, \LibreNMS\Enum\Sensor::Temperature, $device, $oid, 1, 'serveriron-temp', $descr, '2', '1', null, null, $high_warn_limit, $high_limit, $current);
}
