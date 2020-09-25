<?php 

$created_contact_indexes = false;
$created_relay_indexes = false;
$created_power_indexes = false;
foreach ($pre_cache['esPointTable'] as $index => $entry) {

    // contact closures
    if ($entry['esIndexPC'] == 2 && $entry['esPointName'] != 'unnamed') {
        
        if (!$created_contact_indexes) {
            $state_name = 'contactClosure';
            $states = [
                ['value' => 1, 'generic' => 2, 'graph' => 1, 'descr' => 'event'],
                ['value' => 2, 'generic' => 0, 'graph' => 1, 'descr' => 'normal']
            ];
            create_state_index($state_name, $states);
            $created_contact_indexes = true;
        }

        discover_sensor(
            $valid['sensor'],
            'state',
            $device,
            $pre_cache['oid_prefix'] . '.1.1.1.1.5.' . $index,
            $index,
            $state_name,
            $entry['esPointName'],
            1,
            1,
            null,
            null,
            null,
            null,
            $entry['esPointInEventState'],
			'snmp',
			null,
			null,
			null,
			'Contact Closures'
        );

        create_sensor_to_state_index($device, $state_name, $index);
    }

    if ($entry['esIndexPC'] == 6 && $entry['esPointName'] != 'unnamed') {

        // relay outputs
        if (stripos($entry['esPointValueStr'], 'active') === true) {

            if (!$created_relay_indexes) {
                $state_name = 'relayOutput';
                $states = [
                    ['value' => 0, 'generic' => 0, 'graph' => 1, 'descr' => 'de-energized'],
                    ['value' => 1, 'generic' => 2, 'graph' => 1, 'descr' => 'energized']
                ];
                create_state_index($state_name, $states);
                $created_relay_indexes = true;
            }

            discover_sensor(
                $valid['sensor'],
                'state',
                $device,
                $pre_cache['oid_prefix'] . '.1.1.1.1.6.' . $index,
                $index,
                $state_name,
                $entry['esPointName'],
                1,
                1,
                null,
                null,
                null,
                null,
                $entry['esPointValueInt'],
                'snmp',
                null,
                null,
                null,
                'Relay Outputs'
            );

        // power outputs
        } elseif (stripos($entry['esPointValueStr'], 'on') === true || stripos($entry['esPointValueStr'], 'off') === true) {

            if (!$created_power_indexes) {
                $state_name = 'powerOutput';
                $states = [
                    ['value' => 0, 'generic' => 0, 'graph' => 1, 'descr' => 'de-energized'],
                    ['value' => 1, 'generic' => 2, 'graph' => 1, 'descr' => 'energized']
                ];
                create_state_index($state_name, $states);
                $created_power_indexes = true;
            }

            discover_sensor(
                $valid['sensor'],
                'state',
                $device,
                $pre_cache['oid_prefix'] . '.1.1.1.1.6.' . $index,
                $index,
                $state_name,
                $entry['esPointName'],
                1,
                1,
                null,
                null,
                null,
                null,
                $entry['esPointValueInt'],
                'snmp',
                null,
                null,
                null,
                'Power Outputs'
            );
        }

        create_sensor_to_state_index($device, $state_name, $index);
    }

}
unset($created_contact_indexes, $created_relay_indexes, $created_power_indexes);
