<?php

$oids = snmp_walk($device, 'eqlMemberHealthDetailsTemperatureName', '-OQn', 'EQLMEMBER-MIB', 'equallogic');

/**
 * .1.3.6.1.4.1.12740.2.1.6.1.2.1.329840783.4 = Control module 0 processor
 * .1.3.6.1.4.1.12740.2.1.6.1.2.1.329840783.5 = Control module 0 chipset
 * .1.3.6.1.4.1.12740.2.1.6.1.2.1.329840783.6 = Control module 1 processor
 * .1.3.6.1.4.1.12740.2.1.6.1.2.1.329840783.7 = Control module 1 chipset
 * .1.3.6.1.4.1.12740.2.1.6.1.2.1.329840783.8 = Control module 0 SAS Controller
 * .1.3.6.1.4.1.12740.2.1.6.1.2.1.329840783.9 = Control module 0 SAS Expander
 * .1.3.6.1.4.1.12740.2.1.6.1.2.1.329840783.10 = Control module 0 SES Enclosure
 * .1.3.6.1.4.1.12740.2.1.6.1.2.1.329840783.11 = Control module 1 SAS Controller
 * .1.3.6.1.4.1.12740.2.1.6.1.2.1.329840783.12 = Control module 1 SAS Expander
 * .1.3.6.1.4.1.12740.2.1.6.1.2.1.329840783.17 = Control module 0 Battery
 * .1.3.6.1.4.1.12740.2.1.6.1.2.1.329840783.18 = Control module 1 Battery
 **/
d_echo($oids . "\n");
if (! empty($oids)) {
    echo 'EQLCONTROLLER-MIB ';
    foreach (explode("\n", $oids) as $data) {
        $data = trim($data);
        if (! empty($data)) {
            [$oid,$descr] = explode(' = ', $data, 2);
            $split_oid = explode('.', $oid);
            $num_index = $split_oid[(count($split_oid) - 1)];
            $index = $num_index;
            $part_oid = $split_oid[(count($split_oid) - 2)];
            $num_index = $part_oid . '.' . $num_index;
            $base_oid = '.1.3.6.1.4.1.12740.2.1.6.1.3.1.';
            $oid = $base_oid . $num_index;
            $extra = snmp_get_multi($device, "eqlMemberHealthDetailsTemperatureValue.1.$num_index eqlMemberHealthDetailsTemperatureCurrentState.1.$num_index eqlMemberHealthDetailsTemperatureHighCriticalThreshold.1.$num_index eqlMemberHealthDetailsTemperatureHighWarningThreshold.1.$num_index eqlMemberHealthDetailsTemperatureLowCriticalThreshold.1.$num_index eqlMemberHealthDetailsTemperatureLowWarningThreshold.1.$num_index", '-OQUs', 'EQLMEMBER-MIB', 'equallogic');
            $keys = array_keys($extra);
            $temperature = $extra[$keys[0]]['eqlMemberHealthDetailsTemperatureValue'];
            $low_limit = $extra[$keys[0]]['eqlMemberHealthDetailsTemperatureLowCriticalThreshold'];
            $low_warn = $extra[$keys[0]]['eqlMemberHealthDetailsTemperatureLowWarningThreshold'];
            $high_limit = $extra[$keys[0]]['eqlMemberHealthDetailsTemperatureHighCriticalThreshold'];
            $high_warn = $extra[$keys[0]]['eqlMemberHealthDetailsTemperatureHighWarningThreshold'];
            $index = (100 + $index);

            if ($extra[$keys[0]]['eqlMemberHealthDetailsTemperatureCurrentState'] != 'unknown') {
                discover_sensor($valid['sensor'], 'temperature', $device, $oid, $index, 'snmp', $descr, 1, 1, $low_limit, $low_warn, $high_limit, $high_warn, $temperature);
            }
        }//end if
    }//end foreach
}
