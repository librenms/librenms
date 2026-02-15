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
    $oids = SnmpQuery::walk('LM-SENSORS-MIB::lmTempSensorsEntry')->table(1);

    if (!empty($oids)) {
        d_echo("OpenWrt: Found LM-SENSORS-MIB temperature sensors\n");

        foreach ($oids as $index => $entry) {
            if (!isset($entry['lmTempSensorsValue']) || $entry['lmTempSensorsValue'] <= 0) {
                continue;
            }

            $oid = '.1.3.6.1.4.1.2021.13.16.2.1.3.' . $index;
            $descr = $entry['lmTempSensorsDevice'] ?? 'Sensor ' . $index;
            $current = $entry['lmTempSensorsValue'];

            // LM-SENSORS-MIB returns temperature in millidegrees
            $divisor = 1000;
            $current_celsius = $current / $divisor;

            // High limit defaults to 100°C if not specified
            $limit = $entry['lmTempSensorsLimit'] ?? 100000;
            $limit_celsius = $limit / $divisor;

            discover_sensor(
                $valid['sensor'],
                'temperature',
                $device,
                $oid,
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

unset($oids, $index, $entry, $oid, $descr, $current, $divisor, $limit);
