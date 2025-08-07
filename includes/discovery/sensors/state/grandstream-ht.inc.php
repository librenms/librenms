<?php

/**
 * grandstream-gxw.inc.php
 *
 * LibreNMS state sensor discovery for Grandstream GXW devices
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
 * @copyright  2025 LibreNMS
 * @author     LibreNMS Contributors
 */
echo 'Grandstream HT: ';

$state_name = 'hookStatus';
$states = [
    ['value' => 0, 'generic' => 0, 'graph' => 1, 'descr' => 'On Hook'],
    ['value' => 1, 'generic' => 0, 'graph' => 1, 'descr' => 'Off Hook'],
];

create_state_index($state_name, $states);

$state_lookup = array_column($states, 'value', 'descr');

// Get all hook status values
$status_oid = '.1.3.6.1.4.1.42397.1.2.2.1'; // GS-HT8XX-MIB::hookStatus
$statuses = SnmpQuery::hideMib()->walk([
    'GS-HT8XX-MIB::hookStatus',
])->table(2)[0][0]; // Results from a walk are hookStatus.1.0.0!

if (is_array($statuses)) {
    foreach ($statuses as $index => $entry) {
        $status = $entry;
        $numeric_value = isset($state_lookup[$status]) ? $state_lookup[$status] : $status;
        preg_match('/(\d+)/', $index, $matches);
        $oid = "$status_oid.{$matches[1]}.0.0";
        $descr = "Port {$matches[1]} Hook Status";

        discover_sensor(
            null,
            'state',
            $device,
            $oid,
            $index,
            $state_name,
            $descr,
            1,
            1,
            null,
            null,
            null,
            null,
            $numeric_value,
            'snmp',
            null,
            null,
            null,
            'Hook'
        );
    }
}

$state_name = 'regStatus';
$states = [
    ['value' => 0, 'generic' => 0, 'graph' => 1, 'descr' => 'Registered'],
    ['value' => 1, 'generic' => 2, 'graph' => 1, 'descr' => 'Not Registered'],
];

create_state_index($state_name, $states);

$state_lookup = array_column($states, 'value', 'descr');

// Get all hook status values
$status_oid = '.1.3.6.1.4.1.42397.1.2.2.2'; // GS-HT8XX-MIB::regStatus
$statuses = SnmpQuery::hideMib()->walk([
    'GS-HT8XX-MIB::regStatus',
])->table(2)[0][0]; // Results from a walk are regStatus.1.0.0!

if (is_array($statuses)) {
    foreach ($statuses as $index => $entry) {
        $status = $entry;
        $numeric_value = isset($state_lookup[$status]) ? $state_lookup[$status] : $status;
        preg_match('/(\d+)/', $index, $matches);
        $oid = "$status_oid.{$matches[1]}.0.0";
        $descr = "Port {$matches[1]} Reg Status";

        discover_sensor(
            null,
            'state',
            $device,
            $oid,
            $index,
            $state_name,
            $descr,
            1,
            1,
            null,
            null,
            null,
            null,
            $numeric_value,
            'snmp',
            null,
            null,
            null,
            'Registration'
        );
    }
}

unset($statuses, $states, $state_lookup, $states, $statuses);
