<?php

if ($device['os'] == 'hirschmann') {
    echo 'Hirschmann : ';
    $descr = 'Processor';
    $usage = snmp_get($device, 'HMPRIV-MGMT-SNMP-MIB::hmCpuUtilization.0', '-OvqU');

    if (is_numeric($usage)) {
        discover_processor($valid['processor'], $device, 'HMPRIV-MGMT-SNMP-MIB::hmCpuUtilization.0', '0', 'hirschmann', $descr, '1', $usage, null, null);
    }
}
unset($processors_array);
