<?php

// RFC1628 UPS
echo 'RFC1628 ';

$value = snmp_get($device, 'upsEstimatedChargeRemaining.0', '-OvqU', 'UPS-MIB');

if (is_numeric($value)) {
    discover_sensor(
            $valid['sensor'],
            'charge',
            $device,
            '.1.3.6.1.2.1.33.1.2.4.0',
            500,
            'rfc1628',
            'Battery charge remaining',
            1,
            1,
            15,
            50,
            null,
            101,
            $value
        );
}
