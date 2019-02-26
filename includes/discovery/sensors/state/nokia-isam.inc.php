<?php

$snmp_data['nokiaIsamEqpBoardTable'] = snmpwalk_cache_oid($device, 'eqptBoardTable', [], 'ASAM-EQUIP-MIB', 'nokia', '-OQUse');

foreach ($snmp_data['nokiaIsamEqpBoardTable'] as $index => $data) {
    if (is_array($data)) {
        $oid = '.1.3.6.1.4.1.637.61.1.23.3.1.7.' . $index;
        $state_name = 'eqptBoardOperError';
        $current = $data['eqptBoardOperError'];
        $descr = $pre_cache['nokiaIsamSlot'][$index]['numBasedSlot'] . ' ' . $data['eqptSlotActualType'] . ' (' . $data['eqptSlotPlannedType'] . ')';
        $states = array(
            array('value' => 1, 'generic' => 0, 'graph' => 0, 'descr' => 'no-error'),
            array('value' => 2, 'generic' => 1, 'graph' => 0, 'descr' => 'type-mismatch'),
            array('value' => 3, 'generic' => 2, 'graph' => 0, 'descr' => 'board-missing'),
            array('value' => 4, 'generic' => 2, 'graph' => 0, 'descr' => 'board-installation-missing'),
            array('value' => 5, 'generic' => 1, 'graph' => 0, 'descr' => 'no-planned-board'),
            array('value' => 6, 'generic' => 1, 'graph' => 0, 'descr' => 'waiting-for-sw'),
            array('value' => 7, 'generic' => 2, 'graph' => 0, 'descr' => 'init-boot-failed'),
            array('value' => 8, 'generic' => 2, 'graph' => 0, 'descr' => 'init-download-failed'),
            array('value' => 9, 'generic' => 2, 'graph' => 0, 'descr' => 'init-connection-failed'),
            array('value' => 10, 'generic' => 2, 'graph' => 0, 'descr' => 'init-configuration-failed'),
            array('value' => 11, 'generic' => 1, 'graph' => 0, 'descr' => 'board-reset-protection'),
            array('value' => 12, 'generic' => 2, 'graph' => 0, 'descr' => 'invalid-parameter'),
            array('value' => 13, 'generic' => 1, 'graph' => 0, 'descr' => 'temperature-alarm'),
            array('value' => 14, 'generic' => 2, 'graph' => 0, 'descr' => 'tempshutdown'),
            array('value' => 15, 'generic' => 1, 'graph' => 0, 'descr' => 'defense'),
            array('value' => 16, 'generic' => 1, 'graph' => 0, 'descr' => 'board-not-licensed'),
            array('value' => 17, 'generic' => 2, 'graph' => 0, 'descr' => 'sem-power-fail'),
            array('value' => 18, 'generic' => 2, 'graph' => 0, 'descr' => 'sem-ups-fail'),
            array('value' => 19, 'generic' => 2, 'graph' => 0, 'descr' => 'board-in-incompatible-slot'),
            array('value' => 21, 'generic' => 1, 'graph' => 0, 'descr' => 'download-ongoing'),
            array('value' => 255, 'generic' => 2, 'graph' => 0, 'descr' => 'unknown-error'),
        );
        create_state_index($state_name, $states);

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
