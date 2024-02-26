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
        $value = (string) current($pre_cache['raspberry_pi_sensors']['raspberry.' . $codec] ?? []);
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
