<?php

// Base OID for SNMP from ENTITY-SENSOR-MIB
$base_oid = '.1.3.6.1.2.1.99.1.1.1.5.';

// Perform SNMP walk
$temp = SnmpQuery::cache()->walk('ENTITY-SENSOR-MIB::entPhySensorTable')->table(1);

if (is_array($temp)) {
    // Create State Index
    $state_name = 'cumulus_hardware_state';
    $states = [
        ['value' => 1, 'generic' => 0, 'graph' => 0, 'descr' => 'OK'],
        ['value' => 2, 'generic' => 3, 'graph' => 0, 'descr' => 'UNAVAILABLE'],
        ['value' => 3, 'generic' => 2, 'graph' => 0, 'descr' => 'FAILED'],
    ];
    create_state_index($state_name, $states);

    // Define resource class names
    $resource_descr_prefixes = [
        'power_supply' => 'Power Supply',
        'temperature_sensor' => 'Temperature Sensor',
        'fan' => 'Fan',
    ];

    // Iterate over each entry from SNMP walk
    foreach ($temp as $index => $entry) {
        $sensor_value = $entry['ENTITY-SENSOR-MIB::entPhySensorOperStatus'];

        // Determine resource type based on OID index
        if (preg_match('/^1100000\d{2}$/', $index)) {
            $resource_type = 'power_supply';
        } elseif (preg_match('/^1000110\d{2}$/', $index)) {
            $resource_type = 'fan';
        } elseif (preg_match('/^1000000\d{2}$/', $index)) {
            $resource_type = 'temperature_sensor';
        } else {
            continue; // Skip indices not matching specified patterns
        }

        $descr = "{$resource_descr_prefixes[$resource_type]} " . substr($index, -1) . ' State';
        $oid = $base_oid . $index;

        // Discover Sensors
        discover_sensor(null, 'state', $device, $oid, $index, $state_name, $descr, 1, 1, null, null, null, null, $sensor_value, 'snmp', $index);
    }
}