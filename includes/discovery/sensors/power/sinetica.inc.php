<?php
/**
 * sinetica.inc.php
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
 * @copyright  2016 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */
$oids = snmpwalk_cache_oid_num($device, '.1.3.6.1.4.1.13891.101.4.4.1.4', []);

foreach ($oids as $oid => $data) {
    $current_id = substr($oid, strrpos($oid, '.') + 1);

    $current_oid = ".$oid";
    $descr = 'Output';
    if (count($oids) > 1) {
        $descr .= " Phase $current_id";
    }
    $divisor = 10;
    $current = current($data) / $divisor;
    $type = 'sinetica';
    $index = '4.4.1.4.' . $current_id;

    discover_sensor($valid['sensor'], 'power', $device, $current_oid, $index, $type, $descr, $divisor, 1, null, null, null, null, $current);
}
