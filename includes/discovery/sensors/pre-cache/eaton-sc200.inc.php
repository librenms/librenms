<?php

/**
 * eaton-sc200.inc.php
 *
 * Pre-cache the rows in use of the two sparse SC200 tables (126 rectifier
 * slots, 108 digital inputs) instead of walking them.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 *
 * @copyright  2026 Bernhard Steinert
 * @author     Bernhard Steinert <bernhard@steinert.net>
 */

// table => columns that mark a row as in use (not 0) and the columns to fetch
$sparse_tables = [
    'RPS-SC200-MIB::rectifier-Values-Table' => [
        'selectors' => ['RPS-SC200-MIB::rectifier-Serial-Number', 'RPS-SC200-MIB::rectifier-Registration-State'],
        'columns' => [
            'RPS-SC200-MIB::rectifier-Values-Index',
            'RPS-SC200-MIB::rectifier-Serial-Number',
            'RPS-SC200-MIB::rectifier-Status',
            'RPS-SC200-MIB::rectifier-Maximum-Current-Limit',
            'RPS-SC200-MIB::rectifier-Maximum-Power-Limit',
            'RPS-SC200-MIB::rectifier-Minimum-OVSD-Set-Point',
            'RPS-SC200-MIB::rectifier-Maximum-OVSD-Set-Point',
            'RPS-SC200-MIB::rectifier-Reported-AC-Voltage',
            'RPS-SC200-MIB::rectifier-Reported-Voltage',
            'RPS-SC200-MIB::rectifier-Reported-Current',
            'RPS-SC200-MIB::rectifier-Output-Power',
            'RPS-SC200-MIB::rectifier-Heatsink-Temperature',
            'RPS-SC200-MIB::rectifier-Registration-State',
            'RPS-SC200-MIB::rectifier-Failed-State',
            'RPS-SC200-MIB::load-Based-Run-Time',
            'RPS-SC200-MIB::shutdown-Rectifier',
            'RPS-SC200-MIB::rectifier-Has-Been-Shutdown',
            'RPS-SC200-MIB::rectifier-Phase-1',
            'RPS-SC200-MIB::rectifier-Phase-2',
            'RPS-SC200-MIB::rectifier-Phase-3',
        ],
    ],
    'RPS-SC200-MIB::digital-Input-Table' => [
        'selectors' => ['RPS-SC200-MIB::di-Status'],
        'columns' => [
            'RPS-SC200-MIB::digital-Input-Index',
            'RPS-SC200-MIB::di-State',
            'RPS-SC200-MIB::di-Value',
            'RPS-SC200-MIB::di-Status',
            'RPS-SC200-MIB::di-Name',
            'RPS-SC200-MIB::di-IOB-Number',
            'RPS-SC200-MIB::digital-Input-Number',
            'RPS-SC200-MIB::di-Severity',
            'RPS-SC200-MIB::di-Recognition-Period',
            'RPS-SC200-MIB::di-Deactivation-Recognition-Period',
            'RPS-SC200-MIB::di-DO-Mapping-A',
            'RPS-SC200-MIB::di-DO-Mapping-B',
            'RPS-SC200-MIB::di-Send-Trap',
            'RPS-SC200-MIB::di-Active-State',
            'RPS-SC200-MIB::di-Group',
            'RPS-SC200-MIB::di-Notes',
        ],
    ],
];

foreach ($sparse_tables as $table => $definition) {
    $in_use = [];
    foreach ($definition['selectors'] as $selector) {
        $response = SnmpQuery::numericIndex()->walk($selector);
        if (! $response->isValid()) {
            continue 2; // leave the table to the yaml walk
        }

        foreach ($response->valuesByIndex() as $index => $row) {
            if (($row[$selector] ?? '0') !== '0') {
                $in_use[$index] = true;
            }
        }
    }

    $pre_cache[$table] = [];
    if (empty($in_use)) {
        continue;
    }

    ksort($in_use, SORT_NUMERIC);
    $oids = [];
    foreach (array_keys($in_use) as $index) {
        foreach ($definition['columns'] as $column) {
            $oids[] = "$column.$index";
        }
    }

    $rows = SnmpQuery::numericIndex()->get($oids);
    if (! $rows->isValid(true)) {
        unset($pre_cache[$table]); // incomplete answer: leave the table to the yaml walk
        continue;
    }

    $rows->valuesByIndex($pre_cache[$table]);
}
