<?php
echo 'DNOS CPU Usage';

$sysObjectId = snmp_get($device, 'SNMPv2-MIB::sysObjectID.0', '-Ovqn');

if ($device['os'] == 'dnos') {
        if (strstr($sysObjectId, '.1.3.6.1.4.1.6027.1.3.')) {
                echo 'S-Series ';
                $proc = snmp_get($device, '.1.3.6.1.4.1.6027.3.10.1.2.9.1.2.1', '-OvQ');
        } elseif (strstr($sysObjectId, '.1.3.6.1.4.1.6027.1.2.')) {
                echo 'C-Series ';
                $proc = snmp_get($device, '.1.3.6.1.4.1.6027.3.8.1.3.7.1.3.1', '-OvQ');
        } elseif (strstr($sysObjectId, '.1.3.6.1.4.1.6027.1.1.')) {
                echo 'E-Series ';
                $proc = snmp_get($device, '.1.3.6.1.4.1.6027.3.1.1.3.7.1.3.1 ', '-OvQ');
        } else {
                preg_match('/(\d*\.\d*)/', snmp_get($device, '.1.3.6.1.4.1.674.10895.5000.2.6132.1.1.1.1.4.9.0', '-OvQ'), $matches);
                $proc = $matches[0];

        }
}
