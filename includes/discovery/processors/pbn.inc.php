<?php
if ($device['os'] == 'pbn') {
    echo 'PBN : ';

    $descr = 'Processor';
    $usage = str_replace(' percent', '', snmp_get($device, 'NMS-PROCESS-MIB::nmspmCPUTotal5min', '-OvQ'));

    if (is_numeric($usage)) {
        discover_processor($valid['processor'], $device, 'NMS-PROCESS-MIB::nmspmCPUTotal5min', '0', 'pbn-cpu', $descr,
 '100', $usage, null, null);
    }
}
