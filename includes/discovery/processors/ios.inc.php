<?php

if ($device['os_group'] == 'cisco' || $device['os'] == 'acsw') {
    echo 'CISCO-PROCESS-MIB: ';
    $processors_array = snmpwalk_cache_oid($device, 'cpmCPU', null, 'CISCO-PROCESS-MIB');
    d_echo($processors_array);

    foreach ($processors_array as $index => $entry) {
        if (is_numeric($entry['cpmCPUTotal5minRev']) || is_numeric($entry['cpmCPUTotal5min'])) {
            $entPhysicalIndex = $entry['cpmCPUTotalPhysicalIndex'];

            if (isset($entry['cpmCPUTotal5minRev'])) {
                $usage_oid = '.1.3.6.1.4.1.9.9.109.1.1.1.1.8.'.$index;
                $usage     = $entry['cpmCPUTotal5minRev'];
            } elseif (isset($entry['cpmCPUTotal5min'])) {
                $usage_oid = '.1.3.6.1.4.1.9.9.109.1.1.1.1.5.'.$index;
                $usage     = $entry['cpmCPUTotal5min'];
            }

            if ($entPhysicalIndex) {
                $descr_oid = 'entPhysicalName.'.$entPhysicalIndex;
                $descr     = snmp_get($device, $descr_oid, '-Oqv', 'ENTITY-MIB');
            }

            if (!$descr) {
                $descr = "Processor $index";
            }

            // rename old cpmCPU files
            $old_name = array('cpmCPU', $index);
            $new_name = array('processor', 'cpm', $index);
            rrd_file_rename($device, $old_name, $new_name);

            if (!strstr($descr, 'No') && !strstr($usage, 'No') && $descr != '') {
                discover_processor($valid['processor'], $device, $usage_oid, $index, 'cpm', $descr, '1', $usage, $entPhysicalIndex, null);
            }
        }//end if
    }//end foreach

    if (!is_array($valid['processor']['cpm'])) {
        $avgBusy5 = snmp_get($device, '.1.3.6.1.4.1.9.2.1.58.0', '-Oqv');
        if (is_numeric($avgBusy5)) {
            discover_processor($valid['processor'], $device, '.1.3.6.1.4.1.9.2.1.58.0', '0', 'ios', 'Processor', '1', $avgBusy5, null, null);
        }
    }
}//end if

// End Cisco Processors
unset($processors_array);
