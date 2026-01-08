<?php
/**
 * enexus.inc.php
 *
 * Eltek eNexus (Smartpack) current sensor discovery
 *
 * Handles version-specific divisor for SmartpackS devices:
 * - SmartpackS with version < 2.12: divisor 10 (returns 10ths of an amp)
 * - SmartpackS with version >= 2.12: no divisor (returns amps)
 * - Non-SmartpackS devices: no divisor (returns amps)
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 */

// Discover rectifiersCurrent sensor with version-dependent divisor for SmartpackS
$rectifiersCurrent = snmpwalk_cache_oid($device, 'rectifiersCurrent', [], 'SP2-MIB');

if (! empty($rectifiersCurrent)) {
    // Use device array values if OS discovery has already populated them,
    // otherwise query SNMP directly (e.g., when only discovering sensors module)
    $hardware = $device['hardware'] ?? '';
    $version = $device['version'] ?? '';

    if (empty($hardware)) {
        $hardware = snmp_get($device, 'SP2-MIB::controlUnitDescription.1', '-Oqv') ?: '';
        if (empty($hardware)) {
            $hardware = snmp_get($device, 'SP2-MIB::controlUnitDescription.2', '-Oqv') ?: '';
        }
    }
    if (empty($version)) {
        $version = snmp_get($device, 'SP2-MIB::controlUnitSwVersion.1', '-Oqv') ?: '';
        if (empty($version)) {
            $version = snmp_get($device, 'SP2-MIB::controlUnitSwVersion.2', '-Oqv') ?: '';
        }
    }

    // Determine if this is a SmartpackS device
    $isSmartpackS = (bool) preg_match('/^Smart[Pp]ack S/', $hardware);

    // Determine the divisor based on hardware type and version
    // SmartpackS with version < 2.12 returns 10ths of an amp
    // SmartpackS with version >= 2.12 returns amps (no divisor needed)
    // Non-SmartpackS devices return amps (no divisor needed)
    $divisor = 1;
    $descr = 'Rectifier Output Current';

    if ($isSmartpackS) {
        // Compare version - if version < 2.11, use divisor 10
        if (version_compare($version, '2.11', '<')) {
            $divisor = 10;
            $descr = 'System Output Current';
        }
    }

    foreach ($rectifiersCurrent as $index => $entry) {
        $currentValue = $entry['rectifiersCurrentValue'] ?? null;
        $warnLimit = $entry['rectifiersCurrentMinorAlarmLevel'] ?? null;
        $highLimit = $entry['rectifiersCurrentMajorAlarmLevel'] ?? null;

        if ($currentValue !== null) {
            $oid = '.1.3.6.1.4.1.12148.10.5.2.5.' . $index;
            $sensorIndex = 'current.' . $index;

            // Apply divisor to limits as well for consistency
            $warnLimitDivided = $warnLimit !== null ? $warnLimit / $divisor : null;
            $highLimitDivided = $highLimit !== null ? $highLimit / $divisor : null;

            discover_sensor(
                null,
                'current',
                $device,
                $oid,
                $sensorIndex,
                'enexus',
                $descr,
                $divisor,
                1,                    // multiplier
                null,                 // low_limit
                null,                 // low_warn_limit
                $warnLimitDivided,    // warn_limit
                $highLimitDivided,    // high_limit
                $currentValue / $divisor,  // current value
                'snmp',               // poller_type
                null,                 // entPhysicalIndex
                null,                 // entPhysicalIndex_measured
                null,                 // user_func
                'Rectifier'           // group
            );
        }
    }
}
