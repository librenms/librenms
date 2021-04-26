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
$output_oid = '.1.3.6.1.4.1.13891.101.4.2.0';
$output_current = snmp_get($device, $output_oid, '-Oqv');

if (! empty($output_current) || $output_current == 0) {
    $divisor = 10;
    $current = $output_current / $divisor;
    $descr = 'Output';
    $type = 'sinetica';
    $index = '4.2.0';

    discover_sensor($valid['sensor'], 'frequency', $device, $output_oid, $index, $type, $descr, $divisor, '1', null, null, null, null, $current);
}

$bypass_oid = '.1.3.6.1.4.1.13891.101.5.1.0';
$bypass_current = snmp_get($device, $bypass_oid, '-Oqv');

if (! empty($bypass_current) || $bypass_current == 0) {
    $divisor = 10;
    $current = $bypass_current / $divisor;
    $descr = 'Bypass';
    $type = 'sinetica';
    $index = '5.1.0';

    discover_sensor($valid['sensor'], 'frequency', $device, $bypass_oid, $index, $type, $descr, $divisor, '1', null, null, null, null, $current);
}

$oids = snmpwalk_cache_oid_num($device, '.1.3.6.1.4.1.13891.101.3.3.1.2', []);

foreach ($oids as $oid => $data) {
    $current_id = substr($oid, strrpos($oid, '.') + 1);

    $current_oid = ".$oid";
    $descr = 'Input';
    if (count($oids) > 1) {
        $descr .= " Phase $current_id";
    }
    $divisor = 10;
    $current = current($data) / $divisor;
    $type = 'sinetica';
    $index = '3.3.1.2.' . $current_id;

    discover_sensor($valid['sensor'], 'frequency', $device, $current_oid, $index, $type, $descr, $divisor, '1', null, null, null, null, $current);
}
