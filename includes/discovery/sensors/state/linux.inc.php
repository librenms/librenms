<?php
/*
 * codec states for raspberry pi
 * requires snmp extend agent script from librenms-agent
 */
$raspberry = snmp_get($device, 'HOST-RESOURCES-MIB::hrSystemInitialLoadParameters.0', '-Osqnv');

if (preg_match("/(bcm).+(boardrev)/", $raspberry)) {
    $state = "raspberry_codec";
    $oid = '.1.3.6.1.4.1.8072.1.3.2.4.1.2.9.114.97.115.112.98.101.114.114.121.';
    for ($codec = 8; $codec < 14; $codec++) {
        switch ($codec) {
            case "8":
                $descr = "H264 codec";
                break;
            case "9":
                $descr = "MPG2 codec";
                break;
            case "10":
                $descr = "WVC1 codec";
                break;
            case "11":
                $descr = "MPG4 codec";
                break;
            case "12":
                $descr = "MJPG codec";
                break;
            case "13":
                $descr = "WMV9 codec";
                break;
        }
        $value = snmp_get($device, $oid.$codec, '-Oqv');

        if (stripos($value, 'abled') !== false) {
            $state_index_id = create_state_index($state);
            if ($state_index_id) {
                $states = array(
                    array($state_index_id, 'enabled', 1, 2, 0),
                    array($state_index_id, 'disabled', 1, 3 , 2),
                );
            }

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
            discover_sensor($valid['sensor'], 'state', $device, $oid.$codec, $codec, $state, $descr, '1', '1', null, null, null, null, $value, 'snmp', $codec);
            create_sensor_to_state_index($device, $state, $codec);
        }
    }
}
