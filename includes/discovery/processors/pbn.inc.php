<?php
if ($device['os'] == 'pbn') {
    
    // find out wich build number we have
    preg_match('/^.* Build (?<build>\d+)/', $device['version'], $version);
    d_echo($version);

    // specified MIB supported since build 16607
    if ($version[build] >= 16607) {

        echo 'PBN : ';

        $descr = 'Processor';
        $usage = snmp_get($device, 'NMS-PROCESS-MIB::nmspmCPUTotal5min.1', '-OUvQ');

        if (is_numeric($usage)) {
            discover_processor($valid['processor'], $device, 'NMS-PROCESS-MIB::nmspmCPUTotal5min', '0', 'pbn-cpu', $descr, '100', $usage, null, null);
        }
    }
}
