<?php
/**
 * fs-nmu.inc.php
 *
 * -Description-
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
 * @copyright  2020 Jozef Rebjak
 * @author     Jozef Rebjak <jozefrebjak@icloud.com>
 */
$power1 = snmp_get($device, 'power1State.0', '-Ovqe', 'OAP-NMU');
$power2 = snmp_get($device, 'power2State.0', '-Ovqe', 'OAP-NMU');
$fan = snmp_get($device, 'fanState.0', '-Ovqe', 'OAP-NMU');
$oid_power1 = '.1.3.6.1.4.1.40989.10.16.20.11.0';
$oid_power2 = '.1.3.6.1.4.1.40989.10.16.20.12.0';
$oid_fan = '.1.3.6.1.4.1.40989.10.16.20.10.0';
$index = '0';

// Power 1 State
if (is_numeric($power1)) {
    $state_name = 'power1State';
    $states = [
        ['value' => 0, 'generic' => 2, 'graph' => 0, 'descr' => 'off'],
        ['value' => 1, 'generic' => 0, 'graph' => 0, 'descr' => 'on'],
    ];
    create_state_index($state_name, $states);

    $descr = 'Power 1 State';
    discover_sensor($valid['sensor'], 'state', $device, $oid_power1, $index, $state_name, $descr, 1, 1, null, null, null, null, $power1, 'snmp', $index);

    create_sensor_to_state_index($device, $state_name, $index);
}

// Power 2 State
if (is_numeric($power2)) {
    $state_name = 'power2State';
    $states = [
        ['value' => 0, 'generic' => 2, 'graph' => 0, 'descr' => 'off'],
        ['value' => 1, 'generic' => 0, 'graph' => 0, 'descr' => 'on'],
    ];
    create_state_index($state_name, $states);

    $descr = 'Power 2 State';
    discover_sensor($valid['sensor'], 'state', $device, $oid_power2, $index, $state_name, $descr, 1, 1, null, null, null, null, $power2, 'snmp', $index);

    create_sensor_to_state_index($device, $state_name, $index);
}

// Fan State
if (is_numeric($fan)) {
    $state_name = 'fanState';
    $states = [
        ['value' => 0, 'generic' => 2, 'graph' => 0, 'descr' => 'off'],
        ['value' => 1, 'generic' => 0, 'graph' => 0, 'descr' => 'on'],
    ];
    create_state_index($state_name, $states);

    $descr = 'Fan State';
    discover_sensor($valid['sensor'], 'state', $device, $oid_fan, $index, $state_name, $descr, 1, 1, null, null, null, null, $fan, 'snmp', $index);

    create_sensor_to_state_index($device, $state_name, $index);
}
