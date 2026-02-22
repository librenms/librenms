<?php
/**
 * LibreNMS
 *
 * Copyright (c) 2025 LibreNMS Contributors
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 *
 * @package    LibreNMS
 * @link       https://www.librenms.org
 *
 * OpenWrt LM-SENSORS Temperature Sensor Discovery
 *
 * OpenWrt devices can provide temperature sensors via LM-SENSORS-MIB
 * when using the snmpd 'pass' directive with a thermal sensor script.
 * This is commonly used for thermal_zone sensors on ARM SoCs.
 */

if ($device['os'] === 'openwrt') {
    // Query temperature values directly (entire Entry walk times out)
    $temps = SnmpQuery::walk('LM-SENSORS-MIB::lmTempSensorsValue')->table(1);
    
    if (!empty($temps)) {
        d_echo("OpenWrt: Found LM-SENSORS-MIB temperature sensors\n");
        
        // Also get sensor names for better descriptions
        $names = SnmpQuery::walk('LM-SENSORS-MIB::lmTempSensorsDevice')->table(1);

        foreach ($temps as $index => $entry) {
            // SnmpQuery returns keys with MIB prefix
            $current = $entry['LM-SENSORS-MIB::lmTempSensorsValue'] ?? null;
            
            if (!is_numeric($current) || $current <= 0) {
                continue;
            }

            $oid = '.1.3.6.1.4.1.2021.13.16.2.1.3.' . $index;
            $descr = $names[$index]['LM-SENSORS-MIB::lmTempSensorsDevice'] ?? 'Sensor ' . $index;
            
            // LM-SENSORS-MIB returns temperature in millidegrees
            $divisor = 1000;
            $current_celsius = $current / $divisor;

            // High limit defaults to 100°C
            $limit_celsius = 100;

            // Use modern sensor discovery method
            app('sensor-discovery')->discover(new \App\Models\Sensor([
                'poller_type' => 'snmp',
                'sensor_class' => 'temperature',
                'sensor_oid' => $oid,
                'sensor_index' => $index,
                'sensor_type' => 'lm-sensors',
                'sensor_descr' => $descr,
                'sensor_divisor' => $divisor,
                'sensor_multiplier' => 1,
                'sensor_limit_low' => null,
                'sensor_limit_low_warn' => null,
                'sensor_limit_warn' => null,
                'sensor_limit' => $limit_celsius,
                'sensor_current' => $current_celsius,
                'entPhysicalIndex' => null,
                'entPhysicalIndex_measured' => null,
                'user_func' => null,
                'group' => null,
            ]));
        }
    }
}

unset($temps, $names, $index, $entry, $current, $oid, $descr, $divisor, $limit_celsius, $current_celsius);
