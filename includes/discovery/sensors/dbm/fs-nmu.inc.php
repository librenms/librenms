<?php
/**
 * fs-nmu.inc.php
 * 
 * OAP OEO and EDFA Modules for FibreSwitches NMUs
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
 * @link       https://www.librenms.org
 * 
 * @copyright  2021 Priority Colo Inc.
 * @author     Jonathan J Davis <davis@1m.ca>
 * 
 * fs-mnu was originally started by Jozef Rebjak <jozefrebjak@icloud.com> (see git history)
 * 
 */


$oap_dbm_multiplier = 1;
$oap_dbm_divisor = 100;
$oap_flags = '-Ovqe';

 echo "FS NMU OEO Light Levels (dbm)\n";

// OAP C1 -> C16 OEOs 
$oap_oeos = range(1,16);
$oap_oeo_sensors = [
    'TxPower' => [
        'desc' => 'Tx Power',
        'id' => '4',
        'limits' => [
            '10000' => ['low_limit' => -8.2, 'low_warn_limit' => -7.2, 'warn_limit' => 0.25, 'high_limit' => 0.5],
            '80000' => ['low_limit' => 0, 'low_warn_limit' => 1, 'warn_limit' => 3.75, 'high_limit' => 4],
            ],
        ],
    'RxPower' => [
        'desc' => 'Rx Power',
        'id' => '5',
        'limits' => [
            '10000' => ['low_limit' => -14.4, 'low_warn_limit' => -11.4, 'warn_limit' => -0.5, 'high_limit' => 0.5],
            '80000' => ['low_limit' => -23, 'low_warn_limit' => -20 ,'warn_limit' => -10, 'high_limit' => -7],
            ],
        ],
    ];

foreach($oap_oeos as $oap_oeo) {
    $object_ident = 'OAP-C' . $oap_oeo . '-OEO';

    // slots
    $oap_oeo_slots = ['A', 'B', 'C', 'D'];
    $oeo_offset = 11;
    // pairs e.g. A1, A2; B1, B2; C1, C2; D1, D1;
    $oap_oeo_pairs = [1,2];
    foreach($oap_oeo_slots as $slot) {
        foreach($oap_oeo_pairs as $pair) {
            $mode_wave = snmp_get($device, 'vSFP' . $slot . $pair . 'ModeWave.0', $oap_flags, $object_ident);
            if (is_numeric($mode_wave)) {
                $mode_wave = '(' . strval($mode_wave / 100) . 'nm)';
            } else {
                $mode_wave = '';
            }

            $tx_distance = snmp_get($device, 'vSFP' . $slot . $pair . 'ModeTransmissionDistance.0', $oap_flags, $object_ident);

            foreach($oap_oeo_sensors as $sensor => $options) {
                $object_type = 'vSFP' . $slot . $pair . $sensor . '.0';
                $dbm_value = snmp_get($device, $object_type, $oap_flags, $object_ident);
                if (is_numeric($dbm_value)) {
                    $sensor_oid = '.1.3.6.1.4.1.40989.10.16.' . $oap_oeo . '.2.' . $oeo_offset . '.' . $options['id'] . '.0';
                    $sensor_description = 'C' . $oap_oeo . ' OEO ' . $slot . $pair . ' ' . $mode_wave . ' ' . $options['desc'];
                    discover_sensor(
                        $valid['sensor'], 
                        'dbm', 
                        $device, 
                        $sensor_oid,
                        $object_ident . '::' .  $object_type,
                        'fs-nmu', 
                        $sensor_description,
                        $oap_dbm_divisor,
                        $oap_dbm_multiplier,
                        $options['limits'][$tx_distance]['low_limit'],
                        $options['limits'][$tx_distance]['low_warn_limit'],
                        $options['limits'][$tx_distance]['warn_limit'],
                        $options['limits'][$tx_distance]['high_limit'],
                        $dbm_value,
                        'snmp',
                        null, null, null,
                        $object_ident
                        );
                }
            }
            $oeo_offset++;
        }
    }
}

echo "FS NMU EDFAs Levels (dbm)\n";

// OAP C1 -> C16 EDFAs
$oap_edfas = range(1,16);
$oap_edfa_sensors = [
    'PUMPPower' => [
        'desc' => 'Pump Power',
        'id' => '24',
        'limits' => ['low_limit' => 12.5, 'low_warn_limit' => 16, 'warn_limit' => 18, 'high_limit' => 21.5],
        ],
    'Input' => [
        'desc' => 'Input Power',
        'id' => '28',
        'limits' => ['low_limit' => -23, 'low_warn_limit' => -22, 'warn_limit' => 11, 'high_limit' => +12],
        ],
    'Output' => [
        'desc' => 'Output Power',
        'id' => '29',
        'limits' => ['low_limit' => -6, 'low_warn_limit' => -5, 'warn_limit' => 25, 'high_limit' => 29],
        ],
    ];

foreach($oap_edfas as $oap_edfa) {
    $object_ident = 'OAP-C' . $oap_edfa . '-EDFA';
    foreach($oap_edfa_sensors as $sensor => $options) {
        $object_type = 'v' . $sensor. '.0';
        $dbm_value = snmp_get($device, $object_type, $oap_flags, $object_ident);
        if (is_numeric($dbm_value)) {
            $sensor_oid = '.1.3.6.1.4.1.40989.10.16.' . $oap_edfa . '.1.' .$options['id'] . '.0';
            $sensor_description = 'C' . $oap_edfa . ' EDFA ' . $options['desc'];
            discover_sensor(
                $valid['sensor'], 
                'dbm', 
                $device, 
                $sensor_oid,
                $object_ident . '::' .  $object_type,
                'fs-nmu', 
                $sensor_description,
                $oap_dbm_divisor,
                $oap_dbm_multiplier,
                $options['limits']['low_limit'],
                $options['limits']['low_warn_limit'],
                $options['limits']['warn_limit'],
                $options['limits']['high_limit'],
                $dbm_value,
                'snmp',
                null, null, null,
                $object_ident
                );
        }
    }
}