<?php

$chas_oid = '.1.3.6.1.4.1.6486.800.1.1.1.1.1.1.1.2.'; // chasEntPhysOperStatus
$stack_left = snmp_walk($device, 'chasFreeSlots', '-OQUse', 'ALCATEL-IND1-CHASSIS-MIB', 'nokia');
$stack_role = snmp_walk($device, 'alaStackMgrChasRole', '-OQUse', 'ALCATEL-IND1-STACK-MANAGER-MIB', 'nokia');
$stack_alone = substr($stack_role, strpos($stack_role, '=') + 1);
$stack_left = substr($stack_left, strpos($stack_left, '=') + 1);
$true_stacking = (7 - $stack_left);
$stacking = '7';
$stacking_non = '4';
$aos6_fan_oids = snmpwalk_cache_multi_oid($device, 'alaChasEntPhysFanTable', [], 'ALCATEL-IND1-CHASSIS-MIB', 'aos6', '-OQUse');
foreach ($aos6_fan_oids as $index => $data) {
    if (is_array($data)) {
        $oid = '.1.3.6.1.4.1.6486.800.1.1.1.3.1.1.11.1.2.' . $index;
        $state_name = 'alaChasEntPhysFanStatus';
        $current = $data['alaChasEntPhysFanStatus'];
        [$revindex, $revchass, $revdata,] = explode('.', strrev($oid), 4);
        $chassis = strrev($revchass);
        $indexName = strrev($revindex);
        $descr = 'Chassis-' . ($chassis - 568) . " Fan $indexName";
        $states = [
            ['value' => 0, 'generic' => 1, 'graph' => 1, 'descr' => 'noStatus'],
            ['value' => 1, 'generic' => 1, 'graph' => 1, 'descr' => 'notRunning'],
            ['value' => 2, 'generic' => 0, 'graph' => 1, 'descr' => 'running'],
        ];
        if (! empty($current)) {
            create_state_index($state_name, $states);
            discover_sensor($valid['sensor'], 'state', $device, $oid, $index, $state_name, $descr, 1, 1, null, null, null, null, $current);
            create_sensor_to_state_index($device, $state_name, $index);
        }
    }
}
unset(
    $aos6_fan_oids,
    $index,
    $data,
    $descr
);

$aos6_stack_oids = snmpwalk_cache_multi_oid($device, 'alaStackMgrChassisTable', [], 'ALCATEL-IND1-STACK-MANAGER-MIB', 'aos6', '-OQUse');
if (($stack_left < $stacking) && ($stack_alone < $stacking_non)) {
    foreach ($aos6_stack_oids as $stackindexa => $stack_data_a) {
        if (is_array($stack_data_a)) {
            $oid_stackport_a = '.1.3.6.1.4.1.6486.800.1.2.1.24.1.1.1.1.4.' . $stackindexa;
            $current_stacka = $stack_data_a['alaStackMgrLocalLinkStateA'];
            $stack_state_namea = 'alaStackMgrLocalLinkStateA';
            $descr_stacka = 'Stack Port A Chassis-' . "$stackindexa";
            $states_stacka = [
                ['value' => 1, 'generic' => 0, 'graph' => 1, 'descr' => 'Connected'],
                ['value' => 2, 'generic' => 2, 'graph' => 1, 'descr' => 'Disconnected'],
            ];
            create_state_index($stack_state_namea, $states_stacka);
            discover_sensor($valid['sensor'], 'state', $device, $oid_stackport_a, $stackindexa, $stack_state_namea, $descr_stacka, 1, 1, null, null, null, null, $current_stacka);
            create_sensor_to_state_index($device, $stack_state_namea, $stackindexa);
        }
    }
}

if (($stack_left < $stacking) && ($stack_alone < $stacking_non)) {
    foreach ($aos6_stack_oids as $stackindexb => $stack_data_b) {
        if (is_array($stack_data_b)) {
            $oid_stackport_b = '.1.3.6.1.4.1.6486.800.1.2.1.24.1.1.1.1.7.' . $stackindexb;
            $current_stackb = $stack_data_b['alaStackMgrLocalLinkStateB'];
            $stack_state_nameb = 'alaStackMgrLocalLinkStateB';
            $descr_stackb = 'Stack Port B Chassis-' . "$stackindexb";
            $states_stackb = [
                ['value' => 1, 'generic' => 0, 'graph' => 1, 'descr' => 'Connected'],
                ['value' => 2, 'generic' => 2, 'graph' => 1, 'descr' => 'Disconnected'],
            ];
            create_state_index($stack_state_nameb, $states_stackb);
            discover_sensor($valid['sensor'], 'state', $device, $oid_stackport_b, $stackindexb, $stack_state_nameb, $descr_stackb, 1, 1, null, null, null, null, $current_stackb);
            create_sensor_to_state_index($device, $stack_state_nameb, $stackindexb);
        }
    }
}

unset($aos6_stack_oids);
