<?php
/**
 * enlogic-pdu.inc.php
 *
 * LibreNMS sensors state discovery module for enLOGIC PDU
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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    LibreNMS
 * @link       http://librenms.org
 * @copyright  2017 Neil Lathwood
 * @author     Neil Lathwood <gh+n@laf.io>
 */

foreach ($pre_cache['enlogic_pdu_status'] as $index => $data) {
    if (is_array($data)) {
        $oid = '.1.3.6.1.4.1.38446.1.2.4.1.3.' . $index;
        $state_name = 'pduUnitStatusLoadState';
        $state_index_id = create_state_index($state_name);
        $current = $data['pduUnitStatusLoadState'];

        //Create State Translation
        if ($state_index_id !== null) {
            $states = array(
                array($state_index_id, 'upperCritical', 1, 1, 2),
                array($state_index_id, 'upperWarning', 1, 2, 1),
                array($state_index_id, 'lowerWarning', 1, 3, 1),
                array($state_index_id, 'lowerCritical', 1, 4, 2),
                array($state_index_id, 'normal', 1, 5, 0),
            );
            foreach ($states as $value) {
                $insert = array(
                    'state_index_id' => $value[0],
                    'state_descr' => $value[1],
                    'state_draw_graph' => $value[2],
                    'state_value' => $value[3],
                    'state_generic_value' => $value[4]
                );
                dbInsert($insert, 'state_translations');
            }
        }

        $descr = "Load state #$index";
        //Discover Sensors
        discover_sensor($valid['sensor'], 'state', $device, $oid, $index, $state_name, $descr, '1', '1', null, null, null, null, $current);
        //Create Sensor To State Index
        create_sensor_to_state_index($device, $state_name, $index);
    }
}

foreach ($pre_cache['enlogic_pdu_input'] as $index => $data) {
    if (is_array($data)) {
        $oid = '.1.3.6.1.4.1.38446.1.3.4.1.3.' . $index;
        $tmp_index = $state_name . '.' . $index;
        $state_name = 'pduInputPhaseStatusCurrentState';
        $current = $data['pduInputPhaseStatusCurrentState'];
        $descr = "Current state #$index";
        if (!is_numeric($current)) {
            $state_index_id = create_state_index($state_name);
            //Create State Translation
            if ($state_index_id !== null) {
                $states = array(
                    array($state_index_id, 'upperCritical', 1, 1, 2),
                    array($state_index_id, 'upperWarning', 1, 2, 1),
                    array($state_index_id, 'lowerWarning', 1, 3, 1),
                    array($state_index_id, 'lowerCritical', 1, 4, 2),
                    array($state_index_id, 'normal', 1, 5, 0),
                );
                foreach ($states as $value) {
                    $insert = array(
                        'state_index_id' => $value[0],
                        'state_descr' => $value[1],
                        'state_draw_graph' => $value[2],
                        'state_value' => $value[3],
                        'state_generic_value' => $value[4]
                    );
                    dbInsert($insert, 'state_translations');
                }
            }
            //Discover Sensors
            discover_sensor($valid['sensor'], 'state', $device, $oid, $tmp_index, $state_name, $descr, '1', '1', null, null, null, null, $current);
            //Create Sensor To State Index
            create_sensor_to_state_index($device, $state_name, $tmp_index);
        }

        $oid = '.1.3.6.1.4.1.38446.1.3.4.1.4.' . $index;
        $tmp_index = $state_name . '.' . $index;
        $state_name = 'pduInputPhaseStatusVoltageState';
        $current = $data['pduInputPhaseStatusVoltageState'];
        $descr = "Voltage state #$index";
        if (!is_numeric($current)) {
            $state_index_id = create_state_index($state_name);
            //Create State Translation
            if ($state_index_id !== null) {
                $states = array(
                    array($state_index_id, 'upperCritical', 1, 1, 2),
                    array($state_index_id, 'upperWarning', 1, 2, 1),
                    array($state_index_id, 'lowerWarning', 1, 3, 1),
                    array($state_index_id, 'lowerCritical', 1, 4, 2),
                    array($state_index_id, 'normal', 1, 5, 0),
                );
                foreach ($states as $value) {
                    $insert = array(
                        'state_index_id' => $value[0],
                        'state_descr' => $value[1],
                        'state_draw_graph' => $value[2],
                        'state_value' => $value[3],
                        'state_generic_value' => $value[4]
                    );
                    dbInsert($insert, 'state_translations');
                }
            }
            //Discover Sensors
            discover_sensor($valid['sensor'], 'state', $device, $oid, $tmp_index, $state_name, $descr, '1', '1', null, null, null, null, $current);
            //Create Sensor To State Index
            create_sensor_to_state_index($device, $state_name, $tmp_index);
        }
    }
}

foreach ($pre_cache['enlogic_pdu_circuit'] as $index => $data) {
    if (is_array($data)) {
        $oid = '.1.3.6.1.4.1.38446.1.4.4.1.4.' . $index;
        $state_name = 'pduCircuitBreakerStatusLoadState';
        $state_index_id = create_state_index($state_name);
        $current = $data['pduCircuitBreakerStatusLoadState'];

        if (!is_numeric($current)) {
            //Create State Translation
            if ($state_index_id !== null) {
                $states = array(
                    array($state_index_id, 'upperCritical', 1, 1, 2),
                    array($state_index_id, 'upperWarning', 1, 2, 1),
                    array($state_index_id, 'lowerWarning', 1, 3, 1),
                    array($state_index_id, 'lowerCritical', 1, 4, 2),
                    array($state_index_id, 'normal', 1, 5, 0),
                );
                foreach ($states as $value) {
                    $insert = array(
                        'state_index_id' => $value[0],
                        'state_descr' => $value[1],
                        'state_draw_graph' => $value[2],
                        'state_value' => $value[3],
                        'state_generic_value' => $value[4]
                    );
                    dbInsert($insert, 'state_translations');
                }
            }

            $descr = "Circuit breaker state {$data['pduCircuitBreakerLabel']}";
            //Discover Sensors
            discover_sensor($valid['sensor'], 'state', $device, $oid, $index, $state_name, $descr, '1', '1', null, null, null, null, $current);
            //Create Sensor To State Index
            create_sensor_to_state_index($device, $state_name, $index);
        }
    }
}

unset(
    $index,
    $data
);
