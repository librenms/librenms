<?php
/**
 * extendair.inc.php
 *
 * LibreNMS state discover module for Exalt ExtendAir
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
 * @copyright  2017 Neil Lathwood
 * @author     Neil Lathwood <neil@lathwood.co.uk>
 */

// Common States
$states = [
    ['value' => 0, 'generic' => 0, 'graph' => 1, 'descr' => 'almNORMAL'],
    ['value' => 1, 'generic' => 1, 'graph' => 1, 'descr' => 'almMINOR'],
    ['value' => 2, 'generic' => 2, 'graph' => 1, 'descr' => 'almMAJOR'],
    ['value' => 3, 'generic' => 1, 'graph' => 1, 'descr' => 'almDisable'],
    ['value' => 4, 'generic' => 1, 'graph' => 1, 'descr' => 'almNotAvailable'],
    ['value' => 5, 'generic' => 1, 'graph' => 1, 'descr' => 'almClearChanel'],
    ['value' => 6, 'generic' => 1, 'graph' => 1, 'descr' => 'almNonOccupant'],
];

$sensors = [
    ['num_oid' => '.1.3.6.1.4.1.25651.1.2.4.2.4.1.1.0', 'state_name' => 'remLinkState', 'descr' => 'Link status (far end radio)'],
    ['num_oid' => '.1.3.6.1.4.1.25651.1.2.4.2.3.1.1.0', 'state_name' => 'locLinkState', 'descr' => 'Link status (local radio)'],
    ['num_oid' => '.1.3.6.1.4.1.25651.1.2.4.2.3.1.2.0', 'state_name' => 'locTempAlarm', 'descr' => 'Temperature status (local radio)'],
    ['num_oid' => '.1.3.6.1.4.1.25651.1.2.4.2.4.1.2.0', 'state_name' => 'remTempAlarm', 'descr' => 'Temperature status (far end radio)'],
    ['num_oid' => '.1.3.6.1.4.1.25651.1.2.4.2.4.1.9.0', 'state_name' => 'remLinkSecMismatch', 'descr' => 'Link security status'],
    ['num_oid' => '.1.3.6.1.4.1.25651.1.2.4.2.3.1.15.0', 'state_name' => 'locLinkStateV', 'descr' => 'Vertial link status (local radio)'],
    ['num_oid' => '.1.3.6.1.4.1.25651.1.2.4.2.3.1.16.0', 'state_name' => 'locLinkStateH', 'descr' => 'Horizontal link status (local radio)'],
    ['num_oid' => '.1.3.6.1.4.1.25651.1.2.4.2.4.1.15.0', 'state_name' => 'remLinkStateV', 'descr' => 'Vertial link status (far end radio)'],
    ['num_oid' => '.1.3.6.1.4.1.25651.1.2.4.2.4.1.16.0', 'state_name' => 'remLinkStateH', 'descr' => 'Horizontal link status (far end radio)'],
];

foreach ($sensors as $sensor) {
    $temp = snmp_get($device, $sensor['state_name'] . '.0', '-Ovqe', 'ExaltComProducts');
    $cur_oid = $sensor['num_oid'];

    if (is_numeric($temp)) {
        $state_name = $sensor['state_name'];
        $index = $state_name;
        create_state_index($state_name, $states);

        $descr = $sensor['descr'];
        discover_sensor($valid['sensor'], 'state', $device, $cur_oid, $index, $state_name, $descr, 1, 1, null, null, null, null, $temp);
        create_sensor_to_state_index($device, $state_name, $index);
    }
}

unset(
    $temp,
    $cur_oid,
    $states
);
