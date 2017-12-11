<?php
if ($device['os'] == 'pbn') {
    // find out wich build number we have
    preg_match('/^.* Build (?<build>\d+)/', $device['version'], $version);
    d_echo($version);

    // specified MIB supported since build 16607
    if ($version['build'] >= 16607) {
        echo 'PBN : ';

        $descr = 'Processor';
        $usage = snmp_get($device, 'nmspmCPUTotal5min.1', '-OUvQ', 'NMS-PROCESS-MIB', 'pbn');

        if (is_numeric($usage)) {
            discover_processor($valid['processor'], $device, '.1.3.6.1.4.1.11606.10.9.109.1.1.1.1.5.1', '0', 'pbn-cpu', $descr, 1, $usage, null, null);
        }
    }
}
