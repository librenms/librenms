<?php

/**
 * liebert.inc.php
 *
 * LibreNMS os sensor pre-cache module for Liebert
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
 *
 * @copyright  2026 LibreNMS contributors
 * @author     Andrei-Alexandru Bleortu
 */

/** @var \LibreNMS\OS $os */

// IS-UNITY cards on firmware 8.x serve no LIEBERT-GP-ENVIRONMENTAL-MIB, and
// everything they measure is in lgpFlexibleExtendedTable. Only cooling units
// read it: on UPS and power distribution cards the same rows repeat what
// UPS-MIB and LIEBERT-GP-POWER-MIB report. Setting the key here also keeps the
// YAML pre-cache from walking the table for nothing.
$flexible = 'LIEBERT-GP-FLEXIBLE-MIB::lgpFlexibleExtendedTable';
$pre_cache[$flexible] = [];

if ($os instanceof \LibreNMS\OS\Liebert && $os->isCoolingUnit()) {
    // Cards that serve the environmental MIB also repeat its temperature,
    // humidity and capacity readings in the flexible table.
    echo 'lgpEnvironmental ';
    $next = array_key_first(SnmpQuery::numeric()->next('.1.3.6.1.4.1.476.1.42.3.4')->values()) ?? '';
    $pre_cache['liebert_environmental'] = (int) str_starts_with((string) $next, '.1.3.6.1.4.1.476.1.42.3.4.');

    echo 'lgpFlexibleExtendedTable ';
    $pre_cache[$flexible] = SnmpQuery::numericIndex()->walk($flexible)->valuesByIndex();
    $labels = SnmpQuery::numericIndex()->walk('LIEBERT-GP-FLEXIBLE-MIB::lgpFlexibleEntryDataLabel')->valuesByIndex();

    // Each row carries its own number of decimals, and YAML takes a divisor.
    // The short label names a row, numbered where one reading has instances:
    // the last part of the index is the instance.
    $reading = fn ($index) => substr((string) $index, 0, strrpos((string) $index, '.'));
    $instances = array_count_values(array_map($reading, array_keys($pre_cache[$flexible])));
    foreach ($pre_cache[$flexible] as $index => $row) {
        $places = (int) ($row['LIEBERT-GP-FLEXIBLE-MIB::lgpFlexibleEntryDecimalPosition'] ?? 0);
        $pre_cache['liebert_flexible_divisor'][$index] = 10 ** $places;

        $descr = $labels[$index]['LIEBERT-GP-FLEXIBLE-MIB::lgpFlexibleEntryDataLabel']
            ?? $row['LIEBERT-GP-FLEXIBLE-MIB::lgpFlexibleEntryDataDescription'] ?? $index;
        if ($instances[$reading($index)] > 1) {
            $descr .= ' ' . substr((string) $index, strlen($reading($index)) + 1);
        }
        $pre_cache['liebert_flexible_descr'][$index] = $descr;
    }
}
