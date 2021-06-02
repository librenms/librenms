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
 * @link       https://www.librenms.org
 * @copyright  2021 Priority Colo Inc.
 * @author     Jonathan J Davis <davis@1m.ca>
 * 
 */

echo "FS NMU OEO Temperatures\n";

// OAP C1 -> C16 OEOs 
$oap_oeos = range(1,16);
$oap_oeo_sensors = [
    'ModeTemperature' => ['desc' => 'Mode Temperature', 'flags' => '-Ovqe', 'divisor' => '100', 'multiplier' => '1', 'id' => '9'],
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
            $mode_wave = snmp_get($device, 'vSFP' . $slot . $pair . 'ModeWave.0', '-Ovqe', $object_ident);
            if (is_numeric($mode_wave)) {
                $mode_wave = '(' . strval($mode_wave / 100) . 'nm)';
            } else {
                $mode_wave = '( E nm)';
            }
            foreach($oap_oeo_sensors as $sensor => $options) {
                $object_type = 'vSFP' . $slot . $pair . $sensor . '.0';
                $dbm_value = snmp_get($device, $object_type, $options['flags'], $object_ident);
                if (is_numeric($dbm_value)) {
                    $sensor_oid = '.1.3.6.1.4.1.40989.10.16.' . $oap_oeo . '.2.' . $oeo_offset . '.' . $options['id'] . '.0';
                    $sensor_description = 'C' . $oap_oeo . ' OEO ' . $slot . $pair . ' ' . $mode_wave . ' ' . $options['desc'];
                    discover_sensor(
                        $valid['sensor'], 
                        'temperature', 
                        $device, 
                        $sensor_oid,
                        $object_ident . '::' .  $object_type,
                        'fs-nmu', 
                        $sensor_description,
                        $options['divisor'],
                        $options['multiplier'],
                        null, null, null, null,
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

// OAP C1 -> C16 EDAFs 
echo "FS NMU EDFA Temperatures\n";
$oap_edfas = range(1,16);
$oap_edfa_sensors = [
    'ModuleTemperature' => ['desc' => 'Module Temperature', 'flags' => '-Ovqe', 'divisor' => '100', 'multiplier' => '1', 'id' => '22'],
    'PUMPTemperature' => ['desc' => 'Pump Temperature', 'flags' => '-Ovqe', 'divisor' => '100', 'multiplier' => '1', 'id' => '25'],
    ];

foreach($oap_edfas as $oap_edfa) {
    $object_ident = 'OAP-C' . $oap_edfa . '-EDFA';
    foreach($oap_edfa_sensors as $sensor => $options) {
        $object_type = 'v' . $sensor. '.0';
        $dbm_value = snmp_get($device, $object_type, $options['flags'], $object_ident);
        if (is_numeric($dbm_value)) {
            $sensor_oid = '.1.3.6.1.4.1.40989.10.16.' . $oap_edfa . '.1.' .$options['id'] . '.0';
            $sensor_description = 'C' . $oap_edfa . ' EDFA ' . $options['desc'];
            discover_sensor(
                $valid['sensor'], 
                'temperature', 
                $device, 
                $sensor_oid,
                $object_ident . '::' .  $object_type,
                'fs-nmu', 
                $sensor_description,
                $options['divisor'],
                $options['multiplier'],
                null, null, null, null,
                $dbm_value,
                'snmp',
                null, null, null,
                $object_ident
                );
        }
    }
}