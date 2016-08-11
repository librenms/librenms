<?php
if($device['os'] == "hpblmos")
{
    $index = 1;
    $state_name = 'hpblmos_FansState';
    $state_descr = 'Fan ';
    if ($device['hardware'] == "BladeSystem c7000 Enclosure")
    {
        $oids = array(
           '.1.3.6.1.4.1.232.22.2.3.1.3.1.11.1',
           '.1.3.6.1.4.1.232.22.2.3.1.3.1.11.2',
           '.1.3.6.1.4.1.232.22.2.3.1.3.1.11.3',
           '.1.3.6.1.4.1.232.22.2.3.1.3.1.11.4',
           '.1.3.6.1.4.1.232.22.2.3.1.3.1.11.5',
           '.1.3.6.1.4.1.232.22.2.3.1.3.1.11.6',
           '.1.3.6.1.4.1.232.22.2.3.1.3.1.11.7',
           '.1.3.6.1.4.1.232.22.2.3.1.3.1.11.8',
           '.1.3.6.1.4.1.232.22.2.3.1.3.1.11.9',
           '.1.3.6.1.4.1.232.22.2.3.1.3.1.11.10',
        );
    } elseif ($device['hardware'] == "BladeSystem c3000 Enclosure") {
        $oids = array(
           '.1.3.6.1.4.1.232.22.2.3.1.3.1.11.1',
           '.1.3.6.1.4.1.232.22.2.3.1.3.1.11.2',
           '.1.3.6.1.4.1.232.22.2.3.1.3.1.11.3',
           '.1.3.6.1.4.1.232.22.2.3.1.3.1.11.4',
           '.1.3.6.1.4.1.232.22.2.3.1.3.1.11.5',
           '.1.3.6.1.4.1.232.22.2.3.1.3.1.11.6',
        );
    }    

    /* CPQRACK-MIB::cpqRackCommonEnclosureFanTable
     * 1 - other
     * 2 - ok 
     * 3 - degraded
     * 4 - failed
     */

    foreach($oids as $oid) {
        $state = snmp_get($device, $oid, '-Oqv');
        $descr = $state_descr . $index;

        if(!empty($state))
        {
            $state_index_id = create_state_index($state_name);

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

            discover_sensor($valid['sensor'], 'state', $device, $oid, $index, $state_name, $descr, '1', '1', null, null, null, null, $state, 'snmp', $index);
            create_sensor_to_state_index($device, $state_name, $index);
            $index++;
        }
    }

    $index = 1;
    $state_name = 'hpblmos_PowerSupplyState';
    $state_descr = 'Power supply ';
    $oids = array(
        '.1.3.6.1.4.1.232.22.2.5.1.1.1.17.1',
        '.1.3.6.1.4.1.232.22.2.5.1.1.1.17.2',
        '.1.3.6.1.4.1.232.22.2.5.1.1.1.17.3',
        '.1.3.6.1.4.1.232.22.2.5.1.1.1.17.4',
        '.1.3.6.1.4.1.232.22.2.5.1.1.1.17.5',
        '.1.3.6.1.4.1.232.22.2.5.1.1.1.17.6',
    );

    /* CPQRACK-MIB::cpqRackPowerSupplyTable
     * 1 - other
     * 2 - ok
     * 3 - degraded
     * 4 - failed
     */

    foreach($oids as $oid) {
        $state = snmp_get($device, $oid, '-Oqv');
        $descr = $state_descr . $index;

        if(!empty($state))
        {
            $state_index_id = create_state_index($state_name);

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

            discover_sensor($valid['sensor'], 'state', $device, $oid, $index, $state_name, $descr, '1', '1', null, null, null, null, $state, 'snmp', $index);
            create_sensor_to_state_index($device, $state_name, $index);
            $index++;
        }
    }
}
