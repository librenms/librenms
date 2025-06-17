<?php

/**
 * draytek.php
 *
 * DrayTek OS
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
 * @copyright  2025 CTNET BV
 * @author     Rudy Broersma <r.broersma@ctnet.nl>
 */
$lteinfo_objects = SnmpQuery::hideMIB()->walk('DRAYTEK-MIB::lteinfoObjects')->table(1);

$ltestatus_lookup_table = [
    'Detecting' => 0,
    'Initialization' => 1,
    'SIM card ready' => 2,
    'SMS service ready' => 3,
    'Search Network' => 4,
    'Registration denied' => 5,
    'Bridged' => 6,
];

$ltestatus_states = [
    ['value' => 0, 'generic' => 1, 'graph' => 0, 'descr' => 'Detecting'],
    ['value' => 1, 'generic' => 1, 'graph' => 0, 'descr' => 'Initialization'],
    ['value' => 2, 'generic' => 1, 'graph' => 0, 'descr' => 'SIM card ready'],
    ['value' => 3, 'generic' => 1, 'graph' => 0, 'descr' => 'SMS service ready'],
    ['value' => 4, 'generic' => 1, 'graph' => 0, 'descr' => 'Search Network'],
    ['value' => 5, 'generic' => 2, 'graph' => 0, 'descr' => 'Registration denied'],
    ['value' => 6, 'generic' => 0, 'graph' => 0, 'descr' => 'Bridged'],
];

foreach ($lteinfo_objects as $index => $object) {
    if (isset($object['ltestatus'])) {
        $state_name = 'ltestatus';
        create_state_index($state_name, $ltestatus_states);
        $current = $ltestatus_lookup_table[$object['ltestatus']];

        $num_oid = '.1.3.6.1.4.1.7367.4.1.';

        app('sensor-discovery')->discover(new \App\Models\Sensor([
            'poller_type' => 'snmp',
            'sensor_class' => 'state',
            'device_id' => $device['device_id'],
            'sensor_oid' => $num_oid . $index,
            'sensor_index' => 'ltestatus.' . $index,
            'sensor_type' => $state_name,
            'sensor_descr' => 'LTE Status modem ' . $index,
            'sensor_divisor' => 1,
            'sensor_multiplier' => 1,
            'sensor_limit' => null,
            'sensor_limit_warn' => null,
            'sensor_limit_low' => null,
            'sensor_limit_low_warn' => null,
            'sensor_current' => $current,
            'group' => 'Mobile',
        ]));
    }
}
