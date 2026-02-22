<?php
/**
 * LibreNMS
 *
 * OpenWrt LM-SENSORS Temperature Sensor Discovery
 */

use LibreNMS\Snmp\SnmpQuery;

if ($device['os'] === 'openwrt') {
    // Initialize SnmpQuery for the device
    $snmp_query = new SnmpQuery($device);
    
    // Define OID and fetch temperature values from LM-SENSORS-MIB
    $oid_value = '.1.3.6.1.4.1.2021.13.16.2.1.3';
    $oid_name = '.1.3.6.1.4.1.2021.13.16.2.1.2';
    
    // Walk the table for values and device names
    $temps_value = $snmp_query->walk($oid_value);
    $temps_name = $snmp_query->walk($oid_name);

    if (! empty($temps_value)) {
        d_echo("OpenWrt: Found LM-SENSORS-MIB temperature sensors\n");

        foreach ($temps_value as $full_oid => $current) {
            // Extract the index from the end of the full OID
            $index = last(explode('.', $full_oid));
            
            if (! is_numeric($current) || $current <= 0) {
                continue;
            }

            // Match description from the names walk using the same index
            $descr = $temps_name[$oid_name . '.' . $index] ?? 'Sensor ' . $index;

            // LM-SENSORS-MIB returns temperature in millidegrees
            $divisor = 1000;
            $current_celsius = $current / $divisor;
            $limit_celsius = 100;

            discover_sensor(
                $valid['sensor'],
                'temperature',
                $device,
                $full_oid,
                $index,
                'lm-sensors',
                $descr,
                $divisor,
                1,
                null,
                null,
                null,
                $limit_celsius,
                $current_celsius
            );
        }
    }
}

unset($snmp_query, $oid_value, $oid_name, $temps_value, $temps_name, $index, $current, $full_oid, $descr, $divisor, $limit_celsius, $current_celsius);
