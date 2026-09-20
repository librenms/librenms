<?php

// Eaton MGE UPS (PULSAR M etc.) - battery charge percentage
$charge_oid = '.1.3.6.1.4.1.705.1.5.2.0';
$charge = SnmpQuery::get($charge_oid)->value();

if (is_numeric($charge)) {
    discover_sensor(
        null, 'charge', $device, $charge_oid, 0, 'eaton-mgeups',
        'Battery Charge', 1, 1, 20, 30, null, null, $charge
    );
}
