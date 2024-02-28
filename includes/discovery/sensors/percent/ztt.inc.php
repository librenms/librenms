<?php

/**
 * For ZTT MSJ devices
 */

// Battery pack remaining capacity percentage
discover_sensor(
    $valid['sensor'],
    'percent',
    $device,
    '.1.3.6.1.4.1.49692.1.1.1.1.16.1',
    0,
    'Battery',
    'Batterypackremainingcapacitypercentage',
    1000,
    1,
    null,
    null,
    null,
    null,
    snmp_get($device, '.1.3.6.1.4.1.49692.1.1.1.1.16.1', '-Ovq')
);
