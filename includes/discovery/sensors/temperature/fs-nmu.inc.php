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
 * 
 * @copyright  2022 Priority Colo Inc.
 * @author     Jonathan J Davis <davis@1m.ca>
 */

$oap_flags = '-Ovqe';

echo "FS NMU OEO Temperatures\n";

// OAP C1 -> C16 OEOs 
$oap_oeos = range(1,16);
$oap_oeo_sensors = [
    'ModeTemperature' => ['desc' => 'Mode Temperature', 'id' => '9'],
    ];

foreach($oap_oeos as $oap_oeo) {
    $object_ident = 'OAP-C' . $oap_oeo . '-OEO';

    // Slots in OEO for optics pairs
    $oap_oeo_slots = ['A1', 'A2', 'B1', 'B2', 'C1', 'C2', 'D1', 'D2'];
    $oeo_offset = 11;

    foreach($oap_oeo_slots as $slot) {
        $mode_wave = snmp_get($device, 'vSFP' . $slot . 'ModeWave.0', $oap_flags, $object_ident);
        if (is_numeric($mode_wave)) {
            $mode_wave = '(' . strval($mode_wave / 100) . 'nm)';
            foreach($oap_oeo_sensors as $sensor => $options) {
                $object_type = 'vSFP' . $slot . $sensor . '.0';
                $dbm_value = snmp_get($device, $object_type, $oap_flags, $object_ident);
                if (is_numeric($dbm_value)) {
                    $sensor_oid = '.1.3.6.1.4.1.40989.10.16.' . $oap_oeo . '.2.' . $oeo_offset . '.' . $options['id'] . '.0';
                    $sensor_description = 'C' . $oap_oeo . ' OEO ' . $slot . ' ' . $mode_wave . ' ' . $options['desc'];
                    $index = $device['device_id'] . '::' . $object_ident . '::' .  $object_type;

                    discover_sensor(
                        $valid['sensor'], 
                        'temperature', 
                        $device, 
                        $sensor_oid,
                        $index,
                        'fs-nmu', 
                        $sensor_description,
                        100, // divisor
                        1, // multiplier
                        0, // low_limit
                        5, // low_warn_limit
                        60, // warn_limit
                        70, // high_limit
                        $dbm_value,
                        'snmp',
                        null, null, null,
                        $object_ident
                    );
                }
            }
        }
        $oeo_offset++;
        
    }
}

// OAP C1 -> C16 EDAFs 
echo "FS NMU EDFA Temperatures\n";
$oap_edfas = range(1,16);
$oap_edfa_sensors = [
    'ModuleTemperature' => ['desc' => 'Module Temperature', 'id' => '22'],
    'PUMPTemperature' => ['desc' => 'Pump Temperature', 'id' => '25'],
    ];

foreach($oap_edfas as $oap_edfa) {
    $object_ident = 'OAP-C' . $oap_edfa . '-EDFA';

    foreach($oap_edfa_sensors as $sensor => $options) {
        $object_type = 'v' . $sensor. '.0';
        $dbm_value = snmp_get($device, $object_type, $oap_flags, $object_ident);
        if (is_numeric($dbm_value)) {
            $sensor_oid = '.1.3.6.1.4.1.40989.10.16.' . $oap_edfa . '.1.' .$options['id'] . '.0';
            $sensor_description = 'C' . $oap_edfa . ' EDFA ' . $options['desc'];
            $index = $device['device_id'] . '::' . $object_ident . '::' .  $object_type;

            discover_sensor(
                $valid['sensor'], 
                'temperature', 
                $device, 
                $sensor_oid,
                $index,
                'fs-nmu', 
                $sensor_description,
                100, // divisor
                1, // multiplier
                -5, // low_limit
                5, // low_warn_limit
                45, // warn_limit
                55, // high_limit
                $dbm_value,
                'snmp',
                null, null, null,
                $object_ident
            );
        } else {
            break;
        }
    }
}