<?php

if ($device['os'] == 'hirschmann') {
    echo 'Hirschmann : ';
    $descr = 'Processor';
    $usage = snmp_get($device, 'HMPRIV-MGMT-SNMP-MIB::hmCpuUtilization.0', '-OvqU');

    if (is_numeric($usage)) {
        discover_processor($valid['processor'], $device, '.1.3.6.1.4.1.248.14.2.15.2.1.0', '0', 'hirschmann', $descr, '1', $usage, null, null);
    }
}
unset($processors_array);
