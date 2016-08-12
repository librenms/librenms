<?php
if($device['os'] == "hpblmos")
{
    $fan_state_name = 'hpblmos_fanstate';
    $fan_state_descr = 'Fan ';
    $fan_exists_oid = '.1.3.6.1.4.1.232.22.2.3.1.3.1.8.';
    $fan_state_oid = '.1.3.6.1.4.1.232.22.2.3.1.3.1.11.';

    $psu_state_name = 'hpblmos_psustate';
    $psu_state_descr = 'PSU ';
    $psu_exists_oid = '.1.3.6.1.4.1.232.22.2.5.1.1.1.16.';
    $psu_state_oid = '.1.3.6.1.4.1.232.22.2.5.1.1.1.17.';

    for($fanid = 1; $fanid < 11; $fanid++)
    {
        if (snmp_get($device, $fan_exists_oid.$fanid, '-Oqve') != 2)
        {
            $state = snmp_get($device, $fan_state_oid.$fanid, '-Oqv');
            $descr = $fan_state_descr.$fanid;
            
            if (!empty($state))
            {
                $state_index_id = create_state_index($fan_state_name);
                if($state_index_id)
                {
                    $states = array(
                        array($state_index_id, 'other', 0, 1, 3),
                        array($state_index_id, 'ok', 1, 2, 0),
                        array($state_index_id, 'degraded', 1, 3, 1),
                        array($state_index_id, 'failed', 1, 4, 2),
                    );

                    foreach($states as $value) {
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
                discover_sensor($valid['sensor'], 'state', $device, $fan_state_oid.$fanid, $fanid, $fan_state_name, $descr, '1', '1', null, null, null, null, $state, 'snmp', $fanid);
                create_sensor_to_state_index($device, $fan_state_name, $fanid);
            } 
        }
    }

    for($psuid = 1; $psuid < 7; $psuid++)
    {
        if (snmp_get($device, $psu_exists_oid.$psuid, '-Oqve') != 2)
        {
            $state = snmp_get($device, $psu_state_oid.$psuid, '-Oqv');
            $descr = $psu_state_descr.$psuid;

            if (!empty($state))
            {
                $state_index_id = create_state_index($psu_state_name);
                if($state_index_id)
                {
                    $states = array(
                        array($state_index_id, 'other', 0, 1, 3),
                        array($state_index_id, 'ok', 1, 2, 0),
                        array($state_index_id, 'degraded', 1, 3, 1),
                        array($state_index_id, 'failed', 1, 4, 2),
                    );

                    foreach($states as $value) {
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
            discover_sensor($valid['sensor'], 'state', $device, $psu_state_oid.$psuid, $psuid, $psu_state_name, $descr, '1', '1', null, null, null, null, $state, 'snmp', $psuid);
            create_sensor_to_state_index($device, $psu_state_name, $psuid);
        }
    }
}
