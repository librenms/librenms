<?php

if ($device['os'] == 'equallogic') {
    $oids = snmp_walk($device, 'eqlMemberHealthDetailsPowerSupplyName', '-OQn', 'EQLMEMBER-MIB', $config['install_dir'].'/mibs/equallogic');

    /*
        .1.3.6.1.4.1.12740.2.1.8.1.2.1.329840783.1 = Power Cooling Module 0
        .1.3.6.1.4.1.12740.2.1.8.1.2.1.329840783.2 = Power Cooling Module 1
    **/

    d_echo($oids."\n");
    if (!empty($oids)) {
        echo 'EQLCONTROLLER-MIB ';
        foreach (explode("\n", $oids) as $data) {
            $data = trim($data);
            if (!empty($data)) {
                list($oid,$descr) = explode(' = ', $data, 2);
                $split_oid        = explode('.', $oid);
                $num_index        = $split_oid[(count($split_oid) - 1)];
                $index            = $num_index;
                $part_oid         = $split_oid[(count($split_oid) - 2)];
                $num_index        = $part_oid.'.'.$num_index;
                $base_oid         = '.1.3.6.1.4.1.12740.2.1.8.1.3.1.';
                $oid              = $base_oid.$num_index;
                $extra            = snmp_get_multi($device, "eqlMemberHealthDetailsPowerSupplyCurrentState.3.329840783.$index", '-OQUse', 'EQLMEMBER-MIB', $config['install_dir'].'/mibs/equallogic');
                $keys             = array_keys($extra);
                $temperature      = $extra[$keys[0]]['eqlMemberHealthDetailsPowerSupplyValue'];
                $low_limit        = $extra[$keys[0]]['eqlMemberHealthDetailsPowerSupplyLowCriticalThreshold'];
                $low_warn         = $extra[$keys[0]]['eqlMemberHealthDetailsPowerSupplyLowWarningThreshold'];
                $high_limit       = $extra[$keys[0]]['eqlMemberHealthDetailsPowerSupplyHighCriticalThreshold'];
                $high_warn        = $extra[$keys[0]]['eqlMemberHealthDetailsPowerSupplyHighWarningThreshold'];
                $index            = (100 + $index);

                if ($extra[$keys[0]]['eqlMemberHealthDetailsPowerSupplyCurrentState'] != 'unknown') {
                    discover_sensor($valid['sensor'], 'state', $device, $oid, $index, 'snmp', $descr, 1, 1, $low_limit, $low_warn, $high_limit, $high_warn, $temperature);
                }
            }//end if
        }//end foreach
    }//end if
}//end if
