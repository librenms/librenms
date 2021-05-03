<?php

foreach ($pre_cache['aos7_sync_oids'] as $index => $data) {
    if (is_array($data)) {
        $sync_chas_oid = '.1.3.6.1.4.1.6486.801.1.1.1.3.1.1.1.1.4.' . $index;
        $sync_state = 'chasControlCertifyStatus';
        $sync_value = 'chasControlCertifyStatus.1';
        $descr_sync = 'Certify/Restore Status';
        $sync_chas_states = [
            ['value' => 1, 'generic' => 2, 'graph' => 1, 'descr' => 'Unknown'],
            ['value' => 2, 'generic' => 1, 'graph' => 1, 'descr' => 'Need Certify'],
            ['value' => 3, 'generic' => 0, 'graph' => 1, 'descr' => 'Certified'],
        ];
        create_state_index($sync_state, $sync_chas_states);
        discover_sensor($valid['sensor'], 'state', $device, $sync_chas_oid, 1, $sync_state, $descr_sync, 1, 1, null, null, null, null, $sync_value);
        create_sensor_to_state_index($device, $sync_state, 1);
    }
}
unset(
    $sync_chas_oid,
    $sync_state,
    $sync_value,
    $descr_sync,
    $sync_chas_states,
    $data,
    $oids,
    $index
);
foreach ($pre_cache['aos7_sync_oids'] as $index => $data) {
    if (is_array($data)) {
        $sync_chas_oid = '.1.3.6.1.4.1.6486.801.1.1.1.3.1.1.1.1.6.' . $index;
        $sync_state = 'chasControlSynchronizationStatus';
        $sync_value = 'chasControlSynchronizationStatus.1';
        $descr_sync = 'Flash Between CMMs';
        $sync_chas_states = [
            ['value' => 1, 'generic' => 2, 'graph' => 1, 'descr' => 'Unknown'],
            ['value' => 2, 'generic' => 1, 'graph' => 1, 'descr' => 'Mono Control Module'],
            ['value' => 3, 'generic' => 1, 'graph' => 1, 'descr' => 'Not Synchronized'],
            ['value' => 4, 'generic' => 0, 'graph' => 1, 'descr' => 'Synchronized'],
        ];
        create_state_index($sync_state, $sync_chas_states);
        discover_sensor($valid['sensor'], 'state', $device, $sync_chas_oid, 1, $sync_state, $descr_sync, 1, 1, null, null, null, null, $sync_value);
        create_sensor_to_state_index($device, $sync_state, 1);
    }
}
