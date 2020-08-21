<?php
foreach ($pre_cache['aos6_temp_oids'] as $index => $entry) {
    if (is_numeric($entry['chasHardwareBoardTemp']) && $entry['chasHardwareBoardTemp'] != 0) {
        $oid = '.1.3.6.1.4.1.6486.800.1.1.1.3.1.1.3.1.4.' . $index ;
        $value = $entry['chasHardwareBoardTemp'];
        $limit = $entry['chasDangerTempThreshold'];
        $warn_limit = $entry['chasTempThreshold'];
        $descr = "Chassis-".($index-568)." Temperature";

        discover_sensor($valid['sensor'], 'temperature', $device, $oid, $index, 'aos6', $descr, 1, 1, $limit_low, $warn_limit_low, $warn_limit, $limit, $value, 'snmp');
    }
}
