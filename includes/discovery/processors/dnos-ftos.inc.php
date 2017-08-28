<?php
if ($device['os'] == 'dnos' || $device['os'] == 'ftos') {
    echo 'DNOS CPU: ';
    $descr = 'CPU';

    if (preg_match('/.6027.1.3.[0-9]+$/', $device['sysObjectID'])) {
        echo 'Dell S Series Chassis';
        $usage = str_replace(' percent', '', snmp_get($device, 'chStackUnitCpuUtil5Sec.1', '-OvQ', 'F10-S-SERIES-CHASSIS-MIB'));
        discover_processor($valid['processor'], $device, '.1.3.6.1.4.1.6027.3.10.1.2.9.1.2.1', '0', $device['os'], $descr, '1', $usage);
    } elseif (preg_match('/.6027.1.2.[0-9]+$/', $device['sysObjectID'])) {
        echo 'Dell C Series Chassis';
        $usage = str_replace(' percent', '', snmp_get($device, 'chRpmCpuUtil5Sec.1', '-OvQ', 'F10-C-SERIES-CHASSIS-MIB'));
        discover_processor($valid['processor'], $device, '.1.3.6.1.4.1.6027.3.8.1.3.7.1.3.1', '0', $device['os'], $descr, '1', $usage);
    } else {
        preg_match('/(\d*\.\d*)/', snmp_get($device, '.1.3.6.1.4.1.674.10895.5000.2.6132.1.1.1.1.4.9.0', '-OvQ'), $matches);
        $usage = $matches[0];
        discover_processor($valid['processor'], $device, '.1.3.6.1.4.1.674.10895.5000.2.6132.1.1.1.1.4.9.0', '0', 'dnos-cpu', $descr, '1', $usage, null, null);
    }
}
