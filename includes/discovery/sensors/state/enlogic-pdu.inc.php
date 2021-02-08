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
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 * @copyright  2017 Neil Lathwood
 * @author     Neil Lathwood <gh+n@laf.io>
 */
foreach ($pre_cache['enlogic_pdu_status'] as $index => $data) {
    if (is_array($data)) {
        $oid = '.1.3.6.1.4.1.38446.1.2.4.1.3.' . $index;
        $state_name = 'pduUnitStatusLoadState';
        $current = $data['pduUnitStatusLoadState'];

        //Create State Translation
        $states = [
            ['value' => 1, 'generic' => 2, 'graph' => 1, 'descr' => 'upperCritical'],
            ['value' => 2, 'generic' => 1, 'graph' => 1, 'descr' => 'upperWarning'],
            ['value' => 3, 'generic' => 1, 'graph' => 1, 'descr' => 'lowerWarning'],
            ['value' => 4, 'generic' => 2, 'graph' => 1, 'descr' => 'lowerCritical'],
            ['value' => 5, 'generic' => 0, 'graph' => 1, 'descr' => 'normal'],
        ];
        create_state_index($state_name, $states);

        $descr = "Load state #$index";
        //Discover Sensors
        discover_sensor($valid['sensor'], 'state', $device, $oid, $index, $state_name, $descr, 1, 1, null, null, null, null, $current);
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
        if (! is_numeric($current)) {
            $states = [
                ['value' => 1, 'generic' => 2, 'graph' => 1, 'descr' => 'upperCritical'],
                ['value' => 2, 'generic' => 1, 'graph' => 1, 'descr' => 'upperWarning'],
                ['value' => 3, 'generic' => 1, 'graph' => 1, 'descr' => 'lowerWarning'],
                ['value' => 4, 'generic' => 2, 'graph' => 1, 'descr' => 'lowerCritical'],
                ['value' => 5, 'generic' => 0, 'graph' => 1, 'descr' => 'normal'],
            ];
            create_state_index($state_name, $states);

            //Discover Sensors
            discover_sensor($valid['sensor'], 'state', $device, $oid, $tmp_index, $state_name, $descr, 1, 1, null, null, null, null, $current);
            //Create Sensor To State Index
            create_sensor_to_state_index($device, $state_name, $tmp_index);
        }

        $oid = '.1.3.6.1.4.1.38446.1.3.4.1.4.' . $index;
        $tmp_index = $state_name . '.' . $index;
        $state_name = 'pduInputPhaseStatusVoltageState';
        $current = $data['pduInputPhaseStatusVoltageState'];
        $descr = "Voltage state #$index";
        if (! is_numeric($current)) {
            $states = [
                ['value' => 1, 'generic' => 2, 'graph' => 1, 'descr' => 'upperCritical'],
                ['value' => 2, 'generic' => 1, 'graph' => 1, 'descr' => 'upperWarning'],
                ['value' => 3, 'generic' => 1, 'graph' => 1, 'descr' => 'lowerWarning'],
                ['value' => 4, 'generic' => 2, 'graph' => 1, 'descr' => 'lowerCritical'],
                ['value' => 5, 'generic' => 0, 'graph' => 1, 'descr' => 'normal'],
            ];
            create_state_index($state_name, $states);

            //Discover Sensors
            discover_sensor($valid['sensor'], 'state', $device, $oid, $tmp_index, $state_name, $descr, 1, 1, null, null, null, null, $current);
            //Create Sensor To State Index
            create_sensor_to_state_index($device, $state_name, $tmp_index);
        }
    }
}

foreach ($pre_cache['enlogic_pdu_circuit'] as $index => $data) {
    if (is_array($data)) {
        $oid = '.1.3.6.1.4.1.38446.1.4.4.1.4.' . $index;
        $state_name = 'pduCircuitBreakerStatusLoadState';
        $current = $data['pduCircuitBreakerStatusLoadState'];

        if (! is_numeric($current)) {
            //Create State Translation
            $states = [
                ['value' => 1, 'generic' => 2, 'graph' => 1, 'descr' => 'upperCritical'],
                ['value' => 2, 'generic' => 1, 'graph' => 1, 'descr' => 'upperWarning'],
                ['value' => 3, 'generic' => 1, 'graph' => 1, 'descr' => 'lowerWarning'],
                ['value' => 4, 'generic' => 2, 'graph' => 1, 'descr' => 'lowerCritical'],
                ['value' => 5, 'generic' => 0, 'graph' => 1, 'descr' => 'normal'],
            ];
            create_state_index($state_name, $states);

            $descr = "Circuit breaker state {$data['pduCircuitBreakerLabel']}";
            //Discover Sensors
            discover_sensor($valid['sensor'], 'state', $device, $oid, $index, $state_name, $descr, 1, 1, null, null, null, null, $current);
            //Create Sensor To State Index
            create_sensor_to_state_index($device, $state_name, $index);
        }
    }
}

unset(
    $index,
    $data
);
