<?php
/**
 * geist-pdu.inc.php
 *
 * LibreNMS power discovery module for Geist PDU
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
    $value = $data['ctrl3ChIECRealPowerA'] / $divisor;
    $current_oid = '.1.3.6.1.4.1.21239.2.25.1.10.';
    $descr = $data['ctrl3ChIECName'] . ' Phase A';
    $oid = $current_oid . $index;
    if ($value > 0) {
        discover_sensor($valid['sensor'], 'power', $device, $oid, 'ctrl3ChIECRealPowerA', 'geist-pdu', $descr, 1, 1, null, null, null, null, $value);
    }

    $value = $data['ctrl3ChIECRealPowerB'] / $divisor;
    $current_oid = '.1.3.6.1.4.1.21239.2.25.1.18.';
    $descr = $data['ctrl3ChIECName'] . ' Phase B';
    $oid = $current_oid . $index;
    if ($value > 0) {
        discover_sensor($valid['sensor'], 'power', $device, $oid, 'ctrl3ChIECRealPowerB', 'geist-pdu', $descr, 1, 1, null, null, null, null, $value);
    }

    $value = $data['ctrl3ChIECRealPowerC'] / $divisor;
    $current_oid = '.1.3.6.1.4.1.21239.2.25.1.26.';
    $descr = $data['ctrl3ChIECName'] . ' Phase C';
    $oid = $current_oid . $index;
    if ($value > 0) {
        discover_sensor($valid['sensor'], 'power', $device, $oid, 'ctrl3ChIECRealPowerC', 'geist-pdu', $descr, 1, 1, null, null, null, null, $value);
    }
}
