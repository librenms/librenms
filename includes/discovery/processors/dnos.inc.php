<?php

$sysObjectId = snmp_get($device, 'SNMPv2-MIB::sysObjectID.0', '-Ovqn');

if ($device['os'] == 'dnos') {
    echo 'DNOS CPU: ';

    $descr = 'CPU';

        if (strstr($sysObjectId, '.1.3.6.1.4.1.6027.1.3.')) {
                echo 'S-Series ';
                $usage = snmp_get($device, '.1.3.6.1.4.1.6027.3.10.1.2.9.1.2.1', '-OvQ');
                discover_processor($valid['processor'], $device, '.1.3.6.1.4.1.6027.3.10.1.2.9.1.2.1', '0', 'dnos-cpu', $descr, '1', $usage, null, null);
        }
        elseif (strstr($sysObjectId, '.1.3.6.1.4.1.6027.1.2.')) {
                echo 'C-Series ';
                $usage = snmp_get($device, '.1.3.6.1.4.1.6027.3.8.1.3.7.1.3.1', '-OvQ');
                discover_processor($valid['processor'], $device, '.1.3.6.1.4.1.6027.3.8.1.3.7.1.3.1', '0', 'dnos-cpu', $descr, '1', $usage, null, null);
        }
        elseif (strstr($sysObjectId, '.1.3.6.1.4.1.6027.1.1.')) {
                echo 'E-Series ';
                $usage = snmp_get($device, '.1.3.6.1.4.1.6027.3.1.1.3.7.1.3.1 ', '-OvQ');
                discover_processor($valid['processor'], $device, '.1.3.6.1.4.1.6027.3.1.1.3.7.1.3.1', '0', 'dnos-cpu', $descr, '1', $usage, null, null);
        }
        else
        {
                preg_match('/(\d*\.\d*)/', snmp_get($device, '.1.3.6.1.4.1.674.10895.5000.2.6132.1.1.1.1.4.9.0', '-OvQ'), $matches);
                $usage = $matches[0];
                discover_processor($valid['processor'], $device, '.1.3.6.1.4.1.674.10895.5000.2.6132.1.1.1.1.4.9.0', '0', 'dnos-cpu', $descr, '1', $usage, null, null);
        }

}
