<?php

$snmp_data['nokiaIsamEqpBoardTable'] = snmpwalk_cache_oid($device, 'eqptBoardTable', [], 'ASAM-EQUIP-MIB', 'nokia', '-OQUse');

foreach ($snmp_data['nokiaIsamEqpBoardTable'] as $index => $data) {
    if (is_array($data)) {
        $oid = '.1.3.6.1.4.1.637.61.1.23.3.1.7.' . $index;
        $state_name = 'eqptBoardOperError';
        $current = $data['eqptBoardOperError'] ?? null;
        // Skip empty and not planned boards / false table entrys
        if ($current == 0 || $data['eqptSlotActualType'] == 'EMPTY' && $data['eqptSlotPlannedType'] == 'NOT_PLANNED') {
            continue;
        }
        $descr = $pre_cache['nokiaIsamSlot'][$index] . ' ' . $data['eqptSlotActualType'] . ' (' . $data['eqptSlotPlannedType'] . ')';
        $states = [
            ['value' => 1, 'generic' => 0, 'descr' => 'no-error'],
            ['value' => 2, 'generic' => 1, 'descr' => 'type-mismatch'],
            ['value' => 3, 'generic' => 2, 'descr' => 'board-missing'],
            ['value' => 4, 'generic' => 2, 'descr' => 'board-installation-missing'],
            ['value' => 5, 'generic' => 1, 'descr' => 'no-planned-board'],
            ['value' => 6, 'generic' => 1, 'descr' => 'waiting-for-sw'],
            ['value' => 7, 'generic' => 2, 'descr' => 'init-boot-failed'],
            ['value' => 8, 'generic' => 2, 'descr' => 'init-download-failed'],
            ['value' => 9, 'generic' => 2, 'descr' => 'init-connection-failed'],
            ['value' => 10, 'generic' => 2, 'descr' => 'init-configuration-failed'],
            ['value' => 11, 'generic' => 1, 'descr' => 'board-reset-protection'],
            ['value' => 12, 'generic' => 2, 'descr' => 'invalid-parameter'],
            ['value' => 13, 'generic' => 1, 'descr' => 'temperature-alarm'],
            ['value' => 14, 'generic' => 2, 'descr' => 'tempshutdown'],
            ['value' => 15, 'generic' => 1, 'descr' => 'defense'],
            ['value' => 16, 'generic' => 1, 'descr' => 'board-not-licensed'],
            ['value' => 17, 'generic' => 2, 'descr' => 'sem-power-fail'],
            ['value' => 18, 'generic' => 2, 'descr' => 'sem-ups-fail'],
            ['value' => 19, 'generic' => 2, 'descr' => 'board-in-incompatible-slot'],
            ['value' => 21, 'generic' => 1, 'descr' => 'download-ongoing'],
            ['value' => 255, 'generic' => 2, 'descr' => 'unknown-error'],
        ];
        create_state_index($state_name, $states);
        if ($pre_cache['nokiaIsamSlot'][$index] == 'ctrl:2/1:' || $pre_cache['nokiaIsamSlot'][$index] == 'lt:2/1/1:' || $pre_cache['nokiaIsamSlot'][$index] == 'lt:2/1/2:') {
            $group = 'Remote Shelf: 2';
        } elseif ($pre_cache['nokiaIsamSlot'][$index] == 'ctrl:3/1:' || $pre_cache['nokiaIsamSlot'][$index] == 'lt:3/1/1:' || $pre_cache['nokiaIsamSlot'][$index] == 'lt:3/1/2:') {
            $group = 'Remote Shelf: 3';
        } elseif ($pre_cache['nokiaIsamSlot'][$index] == 'ctrl:4/1:' || $pre_cache['nokiaIsamSlot'][$index] == 'lt:4/1/1:' || $pre_cache['nokiaIsamSlot'][$index] == 'lt:4/1/2:') {
            $group = 'Remote Shelf: 4';
        } elseif ($pre_cache['nokiaIsamSlot'][$index] == 'ctrl:5/1:' || $pre_cache['nokiaIsamSlot'][$index] == 'lt:5/1/1:' || $pre_cache['nokiaIsamSlot'][$index] == 'lt:5/1/2:') {
            $group = 'Remote Shelf: 5';
        } elseif ($pre_cache['nokiaIsamSlot'][$index] == 'ctrl:6/1:' || $pre_cache['nokiaIsamSlot'][$index] == 'lt:6/1/1:' || $pre_cache['nokiaIsamSlot'][$index] == 'lt:6/1/2:') {
            $group = 'Remote Shelf: 6';
        } elseif ($pre_cache['nokiaIsamSlot'][$index] == 'ctrl:7/1:' || $pre_cache['nokiaIsamSlot'][$index] == 'lt:7/1/1:' || $pre_cache['nokiaIsamSlot'][$index] == 'lt:7/1/2:') {
            $group = 'Remote Shelf: 7';
        } elseif ($pre_cache['nokiaIsamSlot'][$index] == 'ctrl:8/1:' || $pre_cache['nokiaIsamSlot'][$index] == 'lt:8/1/1:' || $pre_cache['nokiaIsamSlot'][$index] == 'lt:8/1/2:') {
            $group = 'Remote Shelf: 8';
        } else {
            $group = $pre_cache['nokiaProductName'];
        }

        //Discover Sensors
        discover_sensor(null, 'state', $device, $oid, $index, $state_name, $descr, 1, 1, null, null, null, null, $current, 'snmp', null, null, null, $group);
    }
}

unset(
    $index,
    $data
);
