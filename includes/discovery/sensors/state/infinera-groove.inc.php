<?php
/**
 * infinera-groove.inc.php
 *
 * LibreNMS state discovery module for Infinera Groove
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
 * @copyright  2019 Nick Hilliard
 * @author     Nick Hilliard <nick@foobar.org>
 */

// create state index
$state_name = 'cardMode';
$states = [
    ['value' => 0, 'generic' => 3, 'graph' => 0, 'descr' => 'notapplicable'],
    ['value' => 1, 'generic' => 0, 'graph' => 0, 'descr' => 'normal'],
    ['value' => 2, 'generic' => 1, 'graph' => 0, 'descr' => 'regen'],
];
create_state_index($state_name, $states);

$num_oid = '.1.3.6.1.4.1.42229.1.2.3.3.1.1.10.';

foreach ($pre_cache['infineragroove_slotTable'] as $index => $data) {
    if (is_array($data) && isset($data['cardMode'])) {
        // discover sensors
        $descr = 'slot-' . str_replace('.', '/', $index) . ' (' . $data['slotActualCardType'] . ')';
        discover_sensor($valid['sensor'], 'state', $device, $num_oid . $index, $index, $state_name, $descr, '1', '1', null, null, null, null, $data['cardMode'], 'snmp', $index);

        // create sensor to state index
        create_sensor_to_state_index($device, $state_name, $index);
    }
}
