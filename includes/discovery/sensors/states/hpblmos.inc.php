<?php
if ($device['os'] == "hpblmos") {
    $fan_state_name = 'hpblmos_fanstate';
    $fan_state_descr = 'Fan ';
    $fans_oid = '.1.3.6.1.4.1.232.22.2.3.1.3.1.8';
    $fan_state_oid = '.1.3.6.1.4.1.232.22.2.3.1.3.1.11.';

    $fans = trim(snmp_walk($device, $fans_oid, '-Osqn'));

    foreach (explode("\n", $fans) as $fan) {
        $fan = trim($fan);
        if ($fan) {
            list($oid, $presence) = explode(' ', $fan, 2);
            if ($presence != 2) {
                $split_oid = explode('.', $oid);
                $current_id = $split_oid[(count($split_oid) - 1)];
                $current_oid = $fan_state_oid.$current_id;
                $descr = $fan_state_descr.$current_id;
                $state = snmp_get($device, $current_oid, '-Oqv');
                if (!empty($state)) {
                    $state_index_id = create_state_index($fan_state_name);
                    if ($state_index_id) {
                        $states = array(
                            array($state_index_id, 'other', 0, 1, 3),
                            array($state_index_id, 'ok', 1, 2, 0),
                            array($state_index_id, 'degraded', 1, 3, 1),
                            array($state_index_id, 'failed', 1, 4, 2),
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
                }
                discover_sensor($valid['sensor'], 'state', $device, $current_oid, $current_id, $fan_state_name, $descr, '1', '1', null, null, null, null, $state, 'snmp', $current_id);
                create_sensor_to_state_index($device, $fan_state_name, $current_id);
            }
        }
    }

    $psu_oid = '.1.3.6.1.4.1.232.22.2.5.1.1.1.16';
    $psu_state_name = 'hpblmos_psustate';
    $psu_state_descr = 'PSU ';
    $psu_state_oid = '.1.3.6.1.4.1.232.22.2.5.1.1.1.17.';

    $psus = trim(snmp_walk($device, $psu_oid, '-Osqn'));

    foreach (explode("\n", $psus) as $psu) {
        $psu = trim($psu);
        if ($psu) {
            list($oid, $presence) = explode(' ', $psu, 2);
            if ($presence != 2) {
                $split_oid = explode('.', $oid);
                $current_id = $split_oid[(count($split_oid) - 1)];
                $current_oid = $psu_state_oid.$current_id;
                $descr = $psu_state_descr.$current_id;
                $state = snmp_get($device, $current_oid, '-Oqv');
                if (!empty($state)) {
                    $state_index_id = create_state_index($psu_state_name);
                    if ($state_index_id) {
                        $states = array(
                            array($state_index_id, 'other', 0, 1, 3),
                            array($state_index_id, 'ok', 1, 2, 0),
                            array($state_index_id, 'degraded', 1, 3, 1),
                            array($state_index_id, 'failed', 1, 4, 2),
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
                }
                discover_sensor($valid['sensor'], 'state', $device, $current_oid, $current_id, $psu_state_name, $descr, '1', '1', null, null, null, null, $state, 'snmp', $current_id);
                create_sensor_to_state_index($device, $psu_state_name, $current_id);
            }
        }
    }
}
