<?php
/**
 * powerlogic.inc.php
 *
 * LibreNMS current sensor discovery module for Schneider PowerLogic
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
$data = $pre_cache['powerlogic_loadCurrentTable'];

if (is_numeric($data['lcIa'][1])) {
    $current_oid = '.1.3.6.1.4.1.3833.1.7.255.15.1.1.2.1.2.1';
    $index = 'lcIa';
    $descr = 'Phase A';
    $current = $data['lcIa'][1];
    discover_sensor($valid['sensor'], 'current', $device, $current_oid, $index, 'powerlogic', $descr, 1, 1, null, null, null, null, $current);
}

if (is_numeric($data['lcIb'][1])) {
    $current_oid = '.1.3.6.1.4.1.3833.1.7.255.15.1.1.2.1.3.1';
    $index = 'lcIb';
    $descr = 'Phase B';
    $current = $data['lcIb'][1];
    discover_sensor($valid['sensor'], 'current', $device, $current_oid, $index, 'powerlogic', $descr, 1, 1, null, null, null, null, $current);
}

if (is_numeric($data['lcIc'][1])) {
    $current_oid = '.1.3.6.1.4.1.3833.1.7.255.15.1.1.2.1.4.1';
    $index = 'lcIc';
    $descr = 'Phase C';
    $current = $data['lcIc'][1];
    discover_sensor($valid['sensor'], 'current', $device, $current_oid, $index, 'powerlogic', $descr, 1, 1, null, null, null, null, $current);
}

unset($data);
