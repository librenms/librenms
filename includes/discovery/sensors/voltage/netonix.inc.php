<?php
/**
 * netonix.inc.php
 *
 * LibreNMS voltages module for Netonix
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
 * @copyright  2016 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */
echo 'Netonix: ';
// NETONIX-SWITCH-MIB::voltageTable .1.3.6.1.4.1.46242.4
$oids = snmpwalk_cache_multi_oid($device, 'voltageTable', [], 'NETONIX-SWITCH-MIB', 'netonix');
if (is_array($oids)) {
    foreach ($oids as $index => $entry) {
        if (is_numeric($entry['voltage']) && is_numeric($index) && $entry['voltage'] > '0') {
            $descr = $entry['voltageDescription'];
            $oid = '.1.3.6.1.4.1.46242.4.1.3.' . $index;
            $current = $entry['voltage'];
            $divisor = 100;
            discover_sensor($valid['sensor'], 'voltage', $device, $oid, $index, $device['os'], $descr, $divisor, '1', null, null, null, null, $current);
        }
    }
}
