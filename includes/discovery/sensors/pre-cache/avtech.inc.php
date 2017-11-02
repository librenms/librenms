<?php
/**
 * avtech.inc.php
 *
 * Grab all data under avtech enterprise oid and process it for yaml consumption
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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    LibreNMS
 * @link       http://librenms.org
 * @copyright  2017 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */


$virtual_tables = array(
    '.1.3.6.1.4.1.20916.1.8.1.1.6', // RoomAlert 32E/W relays
    '.1.3.6.1.4.1.20916.1.8.1.3', // RoomAlert 32E/W switches
);

$data = snmp_walk($device, '.1.3.6.1.4.1.20916.1', '-OQn');
foreach (explode(PHP_EOL, $data) as $line) {
    list($oid, $value) = explode(' = ', $line);

    if (starts_with($oid, $virtual_tables)) {
        // make a virtual table
        $index_pos = strrpos($oid, '.', -3);  // -3 ignores .0 at end
        $index = substr($oid, $index_pos+1);
        $prefix = substr($oid, 0, $index_pos);

        $pre_cache[$prefix][$index] = array(
            'id' => substr($index, 0, -2),  // chop off the .0
            'value' => $value
        );
    } else {
        $pre_cache[$oid] = array($value);
    }
}
