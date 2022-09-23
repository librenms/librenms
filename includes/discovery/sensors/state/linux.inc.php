<?php
/*
 * codec states for raspberry pi
 * requires snmp extend agent script from librenms-agent
 */
if (! empty($pre_cache['raspberry_pi_sensors'])) {
    $state_name = 'raspberry_codec';
    $oid = '.1.3.6.1.4.1.8072.1.3.2.4.1.2.9.114.97.115.112.98.101.114.114.121.';
    for ($codec = 8; $codec < 14; $codec++) {
        switch ($codec) {
            case '8':
                $descr = 'H264 codec';
                break;
            case '9':
                $descr = 'MPG2 codec';
                break;
            case '10':
                $descr = 'WVC1 codec';
                break;
            case '11':
                $descr = 'MPG4 codec';
                break;
            case '12':
                $descr = 'MJPG codec';
                break;
            case '13':
                $descr = 'WMV9 codec';
                break;
        }
        $value = current($pre_cache['raspberry_pi_sensors']['raspberry.' . $codec]);
        if (stripos($value, 'abled') !== false) {
            $states = [
                ['value' => 2, 'generic' => 0, 'graph' => 1, 'descr' => 'enabled'],
                ['value' => 3, 'generic' => 3, 'graph' => 1, 'descr' => 'disabled'],
            ];
            create_state_index($state_name, $states);

            discover_sensor($valid['sensor'], 'state', $device, $oid . $codec, $codec, $state_name, $descr, 1, 1, null, null, null, null, $value, 'snmp', $codec);
            create_sensor_to_state_index($device, $state_name, $codec);
        } else {
            break;
        }
    }
}

if (! empty($pre_cache['ups_nut_sensors'])) {
    /*
    * All the possible states from https://networkupstools.org/docs/developer-guide.chunked/ar01s04.html#_status_data
    */
    $sensors = [
        ['state_name' => 'UPSOnLine', 'genericT' => 0, 'genericF' => 1, 'descr' => 'UPS on line'],
        ['state_name' => 'UPSOnBattery', 'genericT' => 1, 'genericF' => 0, 'descr' => 'UPS on battery'],
        ['state_name' => 'UPSLowBattery', 'genericT' => 2, 'genericF' => 0, 'descr' => 'UPS low battery'],
        ['state_name' => 'UPSHighBattery', 'genericT' => 1, 'genericF' => 0, 'descr' => 'UPS high battery'],
        ['state_name' => 'UPSBatteryReplace', 'genericT' => 1, 'genericF' => 0, 'descr' => 'UPS the battery needs to be replaced'],
        ['state_name' => 'UPSBatteryCharging', 'genericT' => 1, 'genericF' => 0, 'descr' => 'UPS the battery is charging'],
        ['state_name' => 'UPSBatteryDischarging', 'genericT' => 1, 'genericF' => 0, 'descr' => 'UPS the battery is discharging'],
        ['state_name' => 'UPSUPSBypass', 'genericT' => 1, 'genericF' => 0, 'descr' => 'UPS bypass circuit is active'],
        ['state_name' => 'UPSRuntimeCalibration', 'genericT' => 1, 'genericF' => 0, 'descr' => 'UPS is currently performing runtime calibration'],
        ['state_name' => 'UPSOffline', 'genericT' => 2, 'genericF' => 0, 'descr' => 'UPS is offline and is not supplying power to the load'],
        ['state_name' => 'UPSUPSOverloaded', 'genericT' => 2, 'genericF' => 0, 'descr' => 'UPS is overloaded.'],
        ['state_name' => 'UPSUPSBuck', 'genericT' => 1, 'genericF' => 0, 'descr' => 'UPS is trimming incoming voltage'],
        ['state_name' => 'UPSUPSBoost', 'genericT' => 1, 'genericF' => 0, 'descr' => 'UPS is boosting incoming voltage'],
        ['state_name' => 'UPSForcedShutdown', 'genericT' => 2, 'genericF' => 0, 'descr' => 'UPS forced shutdown'],
    ];
    foreach ($sensors as $index => $sensor) {
        $sensor_oid = 9 + $index;
        $oid = '.1.3.6.1.4.1.8072.1.3.2.4.1.2.7.117.112.115.45.110.117.116.' . strval($sensor_oid);
        $value = current($pre_cache['ups_nut_sensors']['ups-nut.' . $sensor_oid]);

        if (! empty($value) or $value == '0') {
            $state_name = $sensor['state_name'];
            $descr = $sensor['descr'];
            $states = [
                ['value' => 0, 'generic' => $sensor['genericF'], 'graph' => 1, 'descr' => 'False'],
                ['value' => 1, 'generic' => $sensor['genericT'], 'graph' => 1, 'descr' => 'True'],
            ];

            create_state_index($state_name, $states);

            //Discover Sensors
            discover_sensor($valid['sensor'], 'state', $device, $oid, $sensor_oid, $state_name, $descr, '1', '1', null, null, null, null, $value, 'snmp', $sensor_oid);

            //Create Sensor To State Index
            create_sensor_to_state_index($device, $state_name, $sensor_oid);
        }
    }
}
