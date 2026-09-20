<?php

// Eaton MGE UPS - battery runtime remaining (seconds -> minutes, divisor 60)
$runtime_oid = '.1.3.6.1.4.1.705.1.5.1.0';
$runtime = SnmpQuery::get($runtime_oid)->value();
if (is_numeric($runtime)) {
    discover_sensor(
        null, 'runtime', $device, $runtime_oid, 0, 'eaton-mgeups',
        'Battery Runtime Remaining', 60, 1, 10, 15, null, null, $runtime / 60
    );
}
