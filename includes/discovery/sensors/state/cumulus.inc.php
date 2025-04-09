<?php

// Base OID for SNMP from ENTITY-SENSOR-MIB
$base_oid = '.1.3.6.1.2.1.99.1.1.1.5.';

// Define valid indices for PSU and fans
$resource_indices = [
    'power_supply' => range(110000001, 110000008),
    'temperature_sensor' => range(100000001, 100000031),
    'fan' => range(100011001, 100011015)
];

// Create State Index
$state_name = 'cumulus_hardware_state';
$states = [
    ['value' => 1, 'generic' => 0, 'graph' => 0, 'descr' => 'OK'],
    ['value' => 2, 'generic' => 3, 'graph' => 0, 'descr' => 'UNAVAILABLE'],
    ['value' => 3, 'generic' => 2, 'graph' => 0, 'descr' => 'FAILED']
];

// Create state index mappings directly
create_state_index($state_name, $states);

// Define resource class names
$resource_descr_prefixes = [
    'power_supply' => 'Power Supply',
    'temperature_sensor' => 'Temperature Sensor',
    'fan' => 'Fan'
];

// Discovery sensors
foreach ($resource_indices as $resource_type => $indices) {
    foreach ($indices as $index) {
        $oid = $base_oid . $index;
        $descr = "{$resource_descr_prefixes[$resource_type]} " . substr($index, -1) . " State";

        $snmp_value = snmp_get($device, $oid);
        if ($snmp_value !== false) {
            discover_sensor(
                $valid['sensor'],
                'state',
                $device,
                $oid,
                $index,
                $state_name,
                $descr,
                '1',
                $snmp_value
            );
        }
    }
}