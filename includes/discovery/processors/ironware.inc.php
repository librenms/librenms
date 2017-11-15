<?php

if ($device['os'] == 'ironware' || $device['os_group'] == 'ironware') {
    echo 'IronWare : ';
    $processors_array = snmpwalk_cache_triple_oid($device, 'snAgentCpuUtilEntry', $processors_array, 'FOUNDRY-SN-AGENT-MIB');
    d_echo($processors_array);

    foreach ($processors_array as $index => $entry) {
        if (($entry['snAgentCpuUtilValue'] || $entry['snAgentCpuUtil100thPercent']) && $entry['snAgentCpuUtilInterval'] == '300') {
            // $entPhysicalIndex = $entry['cpmCPUTotalPhysicalIndex'];
            if (is_numeric($entry['snAgentCpuUtil100thPercent'])) {
                $usage_oid = '.1.3.6.1.4.1.1991.1.1.2.11.1.1.6.'.$index;
                $precision = 100;
                $usage     = $entry['snAgentCpuUtil100thPercent'] / $precision;
            } elseif (is_numeric($entry['snAgentCpuUtilValue'])) {
                $usage_oid = '.1.3.6.1.4.1.1991.1.1.2.11.1.1.4.'.$index;
                $precision = 1;
                $usage     = $entry['snAgentCpuUtilValue'] / $precision;
            }

            list($slot, $instance, $interval) = explode('.', $index);

            $descr_oid   = 'snAgentConfigModuleDescription.'.$entry['snAgentCpuUtilSlotNum'];
            $descr       = snmp_get($device, $descr_oid, '-Oqv', 'FOUNDRY-SN-AGENT-MIB');
            $descr       = str_replace('"', '', $descr);
            list($descr) = explode(' ', $descr);

            $descr = 'Slot '.$entry['snAgentCpuUtilSlotNum'].' '.$descr;
            $descr = $descr.' ['.$instance.']';

            if (!strstr($descr, 'No') && !strstr($usage, 'No') && $descr != '') {
                discover_processor($valid['processor'], $device, $usage_oid, $index, 'ironware', $descr, $precision, $usage, $entPhysicalIndex, null);
            }
        }//end if
    }//end foreach
}//end if

unset($processors_array);
