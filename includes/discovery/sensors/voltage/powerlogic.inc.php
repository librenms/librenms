<?php
/**
 * powerlogic.inc.php
 *
 * LibreNMS voltage sensor discovery module for Schneider PowerLogic
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
$data = $pre_cache['powerlogic_voltageTable'];

if (is_numeric($data['vVab'][1])) {
    $current_oid = '.1.3.6.1.4.1.3833.1.7.255.15.1.1.5.1.2.1';
    $index = 'vVab';
    $descr = 'Line A to B';
    $current = $data['vVab'][1];
    discover_sensor($valid['sensor'], 'voltage', $device, $current_oid, $index, 'powerlogic', $descr, 1, 1, null, null, null, null, $current);
}

if (is_numeric($data['vVbc'][1])) {
    $current_oid = '.1.3.6.1.4.1.3833.1.7.255.15.1.1.5.1.3.1';
    $index = 'vVbc';
    $descr = 'Line B to C';
    $current = $data['vVbc'][1];
    discover_sensor($valid['sensor'], 'voltage', $device, $current_oid, $index, 'powerlogic', $descr, 1, 1, null, null, null, null, $current);
}

if (is_numeric($data['vVca'][1])) {
    $current_oid = '.1.3.6.1.4.1.3833.1.7.255.15.1.1.5.1.4.1';
    $index = 'vVca';
    $descr = 'Line C to A';
    $current = $data['vVca'][1];
    discover_sensor($valid['sensor'], 'voltage', $device, $current_oid, $index, 'powerlogic', $descr, 1, 1, null, null, null, null, $current);
}

if (is_numeric($data['vVan'][1])) {
    $current_oid = '.1.3.6.1.4.1.3833.1.7.255.15.1.1.5.1.6.1';
    $index = 'vVan';
    $descr = 'Line A to neutral';
    $current = $data['vVan'][1];
    discover_sensor($valid['sensor'], 'voltage', $device, $current_oid, $index, 'powerlogic', $descr, 1, 1, null, null, null, null, $current);
}

if (is_numeric($data['vVbn'][1])) {
    $current_oid = '.1.3.6.1.4.1.3833.1.7.255.15.1.1.5.1.7.1';
    $index = 'vVbn';
    $descr = 'Line B to neutral';
    $current = $data['vVbn'][1];
    discover_sensor($valid['sensor'], 'voltage', $device, $current_oid, $index, 'powerlogic', $descr, 1, 1, null, null, null, null, $current);
}

if (is_numeric($data['vVcn'][1])) {
    $current_oid = '.1.3.6.1.4.1.3833.1.7.255.15.1.1.5.1.8.1';
    $index = 'vVcn';
    $descr = 'Line C to neutral';
    $current = $data['vVcn'][1];
    discover_sensor($valid['sensor'], 'voltage', $device, $current_oid, $index, 'powerlogic', $descr, 1, 1, null, null, null, null, $current);
}

unset($data);
