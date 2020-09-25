<?php

$fan_state_name = 'hpblmos_fanstate';
$fan_state_descr = 'Fan ';
$fans_oid = '.1.3.6.1.4.1.232.22.2.3.1.3.1.8';
$fan_state_oid = '.1.3.6.1.4.1.232.22.2.3.1.3.1.11.';

$fans = trim(snmp_walk($device, $fans_oid, '-Osqn'));

foreach (explode("\n", $fans) as $fan) {
    $fan = trim($fan);
    if ($fan) {
        [$oid, $presence] = explode(' ', $fan, 2);
        if ($presence != 2) {
            $split_oid = explode('.', $oid);
            $current_id = $split_oid[(count($split_oid) - 1)];
            $current_oid = $fan_state_oid . $current_id;
            $descr = $fan_state_descr . $current_id;
            $state = snmp_get($device, $current_oid, '-Oqv');
            if (! empty($state)) {
                $states = [
                    ['value' => 1, 'generic' => 3, 'graph' => 0, 'descr' => 'other'],
                    ['value' => 2, 'generic' => 0, 'graph' => 1, 'descr' => 'ok'],
                    ['value' => 3, 'generic' => 1, 'graph' => 1, 'descr' => 'degraded'],
                    ['value' => 4, 'generic' => 2, 'graph' => 1, 'descr' => 'failed'],
                ];
                create_state_index($state_name, $states);
            }
            discover_sensor($valid['sensor'], 'state', $device, $current_oid, $current_id, $fan_state_name, $descr, 1, 1, null, null, null, null, $state, 'snmp', $current_id);
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
        [$oid, $presence] = explode(' ', $psu, 2);
        if ($presence != 2) {
            $split_oid = explode('.', $oid);
            $current_id = $split_oid[(count($split_oid) - 1)];
            $current_oid = $psu_state_oid . $current_id;
            $descr = $psu_state_descr . $current_id;
            $state = snmp_get($device, $current_oid, '-Oqv');
            if (! empty($state)) {
                $states = [
                    ['value' => 1, 'generic' => 3, 'graph' => 0, 'descr' => 'other'],
                    ['value' => 2, 'generic' => 0, 'graph' => 1, 'descr' => 'ok'],
                    ['value' => 3, 'generic' => 1, 'graph' => 1, 'descr' => 'degraded'],
                    ['value' => 4, 'generic' => 2, 'graph' => 1, 'descr' => 'failed'],
                ];
                create_state_index($state_name, $states);
            }
            discover_sensor($valid['sensor'], 'state', $device, $current_oid, $current_id, $psu_state_name, $descr, 1, 1, null, null, null, null, $state, 'snmp', $current_id);
            create_sensor_to_state_index($device, $psu_state_name, $current_id);
        }
    }
}
