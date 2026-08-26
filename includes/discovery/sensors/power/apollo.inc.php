<?php

/**
 * Ribbon/ECI Apollo System Power sensor discovery.
 *
 * This module polls the vendor-specific OID (.1.3.6.1.4.1.5395.3.7.2.1.8.1)
 * to extract the current system power consumption via regex, registering
 * it as a standard 'power' sensor in Watts.
 */
echo 'Apollo Power ';

$oid = '.1.3.6.1.4.1.5395.3.7.2.1.8.1';

$value = \SnmpQuery::options('-OQv')->get($oid)->value();

if ($value !== null && $value !== '' && preg_match('/([-+]?[0-9]*\.?[0-9]+)/', (string) $value, $match)) {
    $current = (float) $match[1];

    discover_sensor(
        null,
        'power',
        $device,
        $oid,
        'apollo-power',
        'apollo',
        'Current Power Consumption',
        1,
        1,
        null,
        null,
        null,
        null,
        $current,
        'snmp'
    );
}
