<?php
/**
 * fs-nmu.inc.php
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
 *
 * @copyright  2020 Jozef Rebjak
 * @author     Jozef Rebjak <jozefrebjak@icloud.com>
 * 
 * @copyright  2022 Priority Colo Inc.
 * @author     Jonathan J Davis <davis@1m.ca>
 */

$oap_state_name = 'OAPAlarmStates';
$oap_states = [
    ['value' => 0, 'generic' => 2, 'graph' => 0, 'descr' => 'alarm'],
    ['value' => 1, 'generic' => 0, 'graph' => 0, 'descr' => 'normal'],
];

create_state_index($oap_state_name, $oap_states);

echo "FS NMU OEO Alarm States\n";

// OAP C1 -> C16 OEOs 
$oap_oeos = range(1,16);
$oap_oeo_sensors = [
    'TxPowerAlarm' => ['desc' => 'Tx Power Alarm', 'flags' => '-Ovqe', 'id' => '10'],
    'RxPowerAlarm' => ['desc' => 'Rx Power Alarm', 'flags' => '-Ovqe', 'id' => '11'],
    'ModeTemperatureAlarm' => ['desc' => 'Mode Temperature Alarm', 'flags' => '-Ovqe', 'id' => '12'],
    ];

foreach($oap_oeos as $oap_oeo) {
    $object_ident = 'OAP-C' . $oap_oeo . '-OEO';

    // Slots in OEO for optics pairs
    $oap_oeo_slots = ['A1', 'A2', 'B1', 'B2', 'C1', 'C2', 'D1', 'D2'];
    $oeo_offset = 11;
    
    foreach($oap_oeo_slots as $slot) {
        $mode_wave = snmp_get($device, 'vSFP' . $slot . $pair . 'ModeWave.0', '-Ovqe', $object_ident);
        if (is_numeric($mode_wave)) {
            $mode_wave = '(' . strval($mode_wave / 100) . 'nm)';
 
            foreach($oap_oeo_sensors as $sensor => $options) {
                $object_type = 'vSFP' . $slot . $pair . $sensor . '.0';
                $dbm_value = snmp_get($device, $object_type, $options['flags'], $object_ident);
                if (is_numeric($dbm_value)) {
                    $sensor_oid = '.1.3.6.1.4.1.40989.10.16.' . $oap_oeo . '.2.' . $oeo_offset . '.' . $options['id'] . '.0';
                    $sensor_description = 'C' . $oap_oeo . ' OEO ' . $slot . $pair . ' ' . $mode_wave . ' ' . $options['desc'];
                    $index = $device['device_id'] . '::' . $object_ident . '::' .  $object_type;

                    discover_sensor(
                        $valid['sensor'], 
                        'state', 
                        $device, 
                        $sensor_oid,
                        $index,
                        'fs-nmu',
                        $sensor_description,
                        1, // div
                        1, // multiply
                        null, null, null, null,
                        $dbm_value,
                        'snmp',
                        null, null, null,
                        $object_ident
                        );

                    create_sensor_to_state_index($device, $oap_state_name, $index);
                }
            }
        }
        $oeo_offset++;
    }
}

echo "FS NMU EDFAs Alarm States\n";

// OAP C1 -> C16 EDFAs
$oap_edfas = range(1,16);
$oap_edfa_sensors = [
    'InputPowerState' => ['desc' => 'Input Power State', 'flags' => '-Ovqe', 'id' => '16'],
    'OutputPowerState' => ['desc' => 'Output Power State', 'flags' => '-Ovqe', 'id' => '17'],
    'ModuleTemperatureState' => ['desc' => 'Module Temperature State', 'flags' => '-Ovqe', 'id' => '18'],
    'PUMPTemperatureState' => ['desc' => 'PUMP Temperature State', 'flags' => '-Ovqe', 'id' => '19'],
    'PUMPCurrentState' => ['desc' => 'PUMP Current State', 'flags' => '-Ovqe', 'id' => '20'],
    ];

foreach($oap_edfas as $oap_edfa) {
    $object_ident = 'OAP-C' . $oap_edfa . '-EDFA';
    foreach($oap_edfa_sensors as $sensor => $options) {
        $object_type = 'v' . $sensor. '.0';
        $dbm_value = snmp_get($device, $object_type, $options['flags'], $object_ident);
        if (is_numeric($dbm_value)) {
            $sensor_oid = '.1.3.6.1.4.1.40989.10.16.' . $oap_edfa . '.1.' .$options['id'] . '.0';
            $sensor_description = 'C' . $oap_edfa . ' EDFA ' . $options['desc'];
            $index = $device['device_id'] . '::' . $object_ident . '::' .  $object_type;

            discover_sensor(
                $valid['sensor'], 
                'state', 
                $device, 
                $sensor_oid,
                $index,
                'fs-nmu',
                $sensor_description,
                1,
                1,
                null, null, null, null,
                $dbm_value,
                'snmp',
                null, null, null,
                $object_ident
            );
            create_sensor_to_state_index($device, $oap_state_name, $index);
        } else {
            break;
        }
    }
}