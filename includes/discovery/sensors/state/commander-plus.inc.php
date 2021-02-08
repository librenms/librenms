<?php
/**
 * commander-plus.inc.php
 *
 * LibreNMS state discovery module for Commander Plus
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
 * @author     Neil Lathwood <gh+n@laf.io>
 */
$start_oid = '.1.3.6.1.4.1.18642.1.2.4';
$state_table = snmpwalk_cache_oid($device, '.1.3.6.1.4.1.18642.1.2.4', [], 'CCPOWER-MIB');
$x = 1;
foreach ($state_table[0] as $state_name => $state_value) {
    //Create State Translation
    $states = [
        ['value' => 1, 'generic' => 2, 'graph' => 1, 'descr' => 'inactive'],
        ['value' => 2, 'generic' => 0, 'graph' => 1, 'descr' => 'active'],
    ];
    create_state_index($state_name, $states);

    $descr = $state_name;
    discover_sensor($valid['sensor'], 'state', $device, $start_oid . '.' . $x . '.0', $state_name, $state_name, $descr, 1, 1, null, null, null, null, $state_value, 'snmp');

    //Create Sensor To State Index
    create_sensor_to_state_index($device, $state_name, $state_name);
    $x++;
}

unset($state_table, $start_oid);
