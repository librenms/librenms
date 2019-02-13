<?php

$snmp_data['nokiaIsamEqpBoardTable'] = snmpwalk_cache_oid($device, 'eqptBoardTable', [], 'ASAM-EQUIP-MIB', 'nokia', '-OQUse');

foreach ($snmp_data['nokiaIsamEqpBoardTable'] as $index => $data) {
    if (is_array($data)) {
        $oid = '.1.3.6.1.4.1.637.61.1.23.3.1.7.' . $index;
        $state_name = 'eqptBoardOperError';
        $state_index_id = create_state_index($state_name);
        $current = $data['eqptBoardOperError'];
        $descr = $pre_cache['nokiaIsamSlot'][$index]['numBasedSlot'] . ' ' . $data['eqptSlotActualType'] . ' (' . $data['eqptSlotPlannedType'] . ')';

        //Create State Translation
        if ($state_index_id !== null) {
            $states = [
                [ 1, 0, 0, $state_index_id, 'no-error' ],
                [ 2, 1, 0, $state_index_id, 'type-mismatch' ],
                [ 3, 2, 0, $state_index_id, 'board-missing' ],
                [ 4, 2, 0, $state_index_id, 'board-installation-missing' ],
                [ 5, 1, 0, $state_index_id, 'no-planned-board' ],
                [ 6, 1, 0, $state_index_id, 'waiting-for-sw' ],
                [ 7, 2, 0, $state_index_id, 'init-boot-failed' ],
                [ 8, 2, 0, $state_index_id, 'init-download-failed' ],
                [ 9, 2, 0, $state_index_id, 'init-connection-failed' ],
                [ 10, 2, 0, $state_index_id, 'init-configuration-failed' ],
                [ 11, 1, 0, $state_index_id, 'board-reset-protection' ],
                [ 12, 2, 0, $state_index_id, 'invalid-parameter' ],
                [ 13, 1, 0, $state_index_id, 'temperature-alarm' ],
                [ 14, 2, 0, $state_index_id, 'tempshutdown' ],
                [ 15, 1, 0, $state_index_id, 'defense' ],
                [ 16, 1, 0, $state_index_id, 'board-not-licensed' ],
                [ 17, 2, 0, $state_index_id, 'sem-power-fail' ],
                [ 18, 2, 0, $state_index_id, 'sem-ups-fail' ],
                [ 19, 2, 0, $state_index_id, 'board-in-incompatible-slot' ],
                [ 21, 1, 0, $state_index_id, 'download-ongoing' ],
                [ 255, 2, 0, $state_index_id, 'unknown-error' ],
            ];
            foreach ($states as $value) {
                $insert = [
                    'state_value' => $value[0],
                    'state_generic_value' => $value[1],
                    'state_draw_graph' => $value[2],
                    'state_index_id' => $value[3],
                    'state_descr' => $value[4],
                ];
                dbInsert($insert, 'state_translations');
            }
        }

        //Discover Sensors
        discover_sensor($valid['sensor'], 'state', $device, $oid, $index, $state_name, $descr, '1', '1', null, null, null, null, $current);
        //Create Sensor To State Index
        create_sensor_to_state_index($device, $state_name, $index);
    }
}

unset(
    $index,
    $data
);
