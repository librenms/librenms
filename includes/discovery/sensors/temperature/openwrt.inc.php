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
 * @link       https://www.librenms.org
 *
 * OpenWrt LM-SENSORS Temperature Sensor Discovery
 *
 * OpenWrt devices can provide temperature sensors via LM-SENSORS-MIB
 * when using the snmpd 'exec' directive with thermal zone files.
 * This is commonly used for thermal_zone sensors on ARM SoCs.
 */

if ($device['os'] === 'openwrt') {
    // Query temperature values from LM-SENSORS-MIB
    $oid = '.1.3.6.1.4.1.2021.13.16.2.1.3';
    $temps = snmpwalk_cache_oid($device, 'lmTempSensorsValue', [], 'LM-SENSORS-MIB');

    if (! empty($temps)) {
        d_echo("OpenWrt: Found LM-SENSORS-MIB temperature sensors\n");

        // Also get sensor names for better descriptions
        $temps = snmpwalk_cache_oid($device, 'lmTempSensorsDevice', $temps, 'LM-SENSORS-MIB');

        foreach ($temps as $index => $entry) {
            $current = $entry['lmTempSensorsValue'] ?? null;

            if (! is_numeric($current) || $current <= 0) {
                continue;
            }

            $full_oid = $oid . '.' . $index;
            $descr = $entry['lmTempSensorsDevice'] ?? 'Sensor ' . $index;

            // LM-SENSORS-MIB returns temperature in millidegrees
            $divisor = 1000;
            $current_celsius = $current / $divisor;

            // High limit defaults to 100°C
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

unset($oid, $temps, $index, $entry, $current, $full_oid, $descr, $divisor, $limit_celsius, $current_celsius);
