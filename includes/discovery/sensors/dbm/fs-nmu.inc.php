<?php
/**
 * fs-nmu.inc.php
 * 
 * OAP OEO and EDFA Modules for Fibreswitches
 * 
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful, 
 * but WITHOUT ANY WARRANTY; without even the implied warranty of 
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see https://www.gnu.org/licenses/.
 * 
 * @copyright  2021 PriorityColo Inc.
 * @author     Jonathan J Davis <davis@1m.ca>
 * 
 * fs-mnu was originally started by (see git for changes):
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
echo 'FS NMU Signals';

// FS Module, OAP C1 -> C16 OEOs 
$oap_oeos = range(1,16);
$oap_oeo_sensors = [
    'TxPower' => ['flags' => '-Ovqe', 'divisor' => '100', 'multiplier' => '1', 'oid' => '4'],
    'RxPower' => ['flags' => '-Ovqe', 'divisor' => '100', 'multiplier' => '1', 'oid' => '5'],
    //'TxPowerAlarm' => ['flags' => '-Ovqe', 'divisor' => '1', 'multiplier' => '1'],
    //'RxPowerAlarm' => ['flags' => '-Ovqe', 'divisor' => '1', 'multiplier' => '1'],
    //'ModeTransmissionRate' => ['flags' => '-Ovqe', 'divisor' => '100', 'multiplier' => '1'],
    //'ModeTransmissionDistance' => ['flags' => '-Ovqe', 'divisor' => '100', 'multiplier' => '1'],
    //'ModeTransmissionRate' => ['flags' => '-Ovqe', 'divisor' => '100', 'multiplier' => '1'],
    ];
foreach($oap_oeos as $oap_oeo) {
    $oap_oeo_mib = 'OAP-C' . $oap_oeo . '-OEO';

    // SLOT A,B,C,D
    $oap_oeo_slots = ['A', 'B', 'C', 'D'];
    $oap_oeo_oid_offset = 11;
    // pairs 1 & 2, e.g. A1, A2, 
    $oap_oeo_pairs = [1,2];
    foreach($oap_oeo_slots as $slot) {
        foreach($oap_oeo_pairs as $pair) {
            foreach($oap_oeo_sensors as $oap_oeo_sensor_index => $oap_oeo_sensor_options) {
                $full_sensor_index = 'vSFP' . $slot . $pair . $oap_oeo_sensor_index . '.0';
                $dbm = snmp_get($device, $full_sensor_index, $oap_oeo_sensor_options['flags'], $oap_oeo_mib);
                if (is_numeric($dbm)) {
                    $sensor_oid = '.1.3.6.1.4.1.40989.10.16.' . $oap_oeo . '.2.' . $oap_oeo_oid_offset . '.' .$oap_oeo_sensor_options['oid'] . '.0';
                    $sensor_description = preg_split('/(?=[A-Z])/', $oap_oeo_sensor_index);
                    $sensor_description = 'C' . $oap_oeo . ' OEO ' . $slot . $pair . ' ' . trim(implode(' ', $sensor_description));
                    discover_sensor(
                        $valid['sensor'], 
                        'dbm', 
                        $device, 
                        $sensor_oid,
                        $oap_oeo_mib . '::' .  $full_sensor_index,
                        'fs-nmu', 
                        $sensor_description,
                        $oap_oeo_sensor_options['divisor'],
                        $oap_oeo_sensor_options['multiplier'],
                        null, null, null, null,
                        $dbm,
                        'snmp'
                        );

                }
            }
            $oap_oeo_oid_offset++;
        }
        $oap_oeo_oid_offset++;
    }
}


// $oap_edfas = range(1,16);
// $oap_edfa_sensors = [
//     'TxPower' => ['flags' => '-Ovqe', 'divisor' => '100', 'multiplier' => '1'],
//     'RxPower' => ['flags' => '-Ovqe', 'divisor' => '100', 'multiplier' => '1'],
// ];