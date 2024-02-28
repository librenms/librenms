<?php

/**
 * For ZTT MSJ devices
 */

// Number of battery packs
discover_sensor(
    $valid['sensor'],
    'count',
    $device,
    '.1.3.6.1.4.1.49692.1.1.1.1.5.1',
    0,
    'Battery',
    'Numberofbatterypacks',
    1000,
    1,
    null,
    null,
    null,
    null,
    snmp_get($device, '.1.3.6.1.4.1.49692.1.1.1.1.5.1', '-Ovq')
);
// Number of rectifier modules
discover_sensor(
    $valid['sensor'],
    'count',
    $device,
    '.1.3.6.1.4.1.49692.1.4.1.1.4.1',
    1,
    'Rectifier',
    'numberofrectifiermodules',
    1,
    1,
    null,
    null,
    null,
    null,
    snmp_get($device, '.1.3.6.1.4.1.49692.1.4.1.1.4.1', '-Ovq')
);
