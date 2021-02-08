<?php
/**
 * geist-pdu.inc.php
 *
 * LibreNMS current discovery module for Geist PDU
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
foreach ($pre_cache['geist_pdu_iec'] as $index => $data) {
    $divisor = 10;
    $value = $data['ctrl3ChIECDeciAmpsA'] / $divisor;
    $current_oid = '.1.3.6.1.4.1.21239.2.25.1.8.';
    $descr = $data['ctrl3ChIECName'] . ' Phase A';
    $oid = $current_oid . $index;
    if ($value > 0) {
        discover_sensor($valid['sensor'], 'current', $device, $oid, 'ctrl3ChIECDeciAmpsA', 'geist-pdu', $descr, $divisor, 1, null, null, null, null, $value);
    }

    $divisor = 10;
    $value = $data['ctrl3ChIECDeciAmpsB'] / $divisor;
    $current_oid = '.1.3.6.1.4.1.21239.2.25.1.16.';
    $descr = $data['ctrl3ChIECName'] . ' Phase B';
    $oid = $current_oid . $index;
    if ($value > 0) {
        discover_sensor($valid['sensor'], 'current', $device, $oid, 'ctrl3ChIECDeciAmpsB', 'geist-pdu', $descr, $divisor, 1, null, null, null, null, $value);
    }

    $divisor = 10;
    $value = $data['ctrl3ChIECDeciAmpsC'] / $divisor;
    $current_oid = '.1.3.6.1.4.1.21239.2.25.1.24.';
    $descr = $data['ctrl3ChIECName'] . ' Phase C';
    $oid = $current_oid . $index;
    if ($value > 0) {
        discover_sensor($valid['sensor'], 'current', $device, $oid, 'ctrl3ChIECDeciAmpsC', 'geist-pdu', $descr, $divisor, 1, null, null, null, null, $value);
    }
}
