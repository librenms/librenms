<?php

// Code borrowed and modified from 'powerconnect-cpu.inc.php'

$sysObjectId = snmp_get($device, 'SNMPv2-MIB::sysObjectID.0', '-Ovqn');

if ($device['os'] == 'dnos') {
    echo 'DNOS-MEMORY-POOL:  ';
        if (strstr($sysObjectId, '.1.3.6.1.4.1.6027.1.3.')) {
                echo 'S-Series ';
                $free = snmp_get($device, '.1.3.6.1.4.1.6027.3.10.1.2.9.1.5.1', '-OvQ');

                if (is_numeric($free)) {
                        discover_mempool($valid_mempool, $device, 0, 'dnos-mem', 'Memory Utilization', '1', null, null);
                }
        } elseif (strstr($sysObjectId, '.1.3.6.1.4.1.6027.1.2.')) {
                echo 'C-Series ';
                $free = snmp_get($device, '.1.3.6.1.4.1.6027.3.8.1.3.7.1.6.1', '-OvQ');

                if (is_numeric($free)) {
                        discover_mempool($valid_mempool, $device, 0, 'dnos-mem', 'Memory Utilization', '1', null, null);
                }
        } elseif (strstr($sysObjectId, '.1.3.6.1.4.1.6027.1.1.')) {
                echo 'E-Series ';
                $free = snmp_get($device, '.1.3.6.1.4.1.6027.3.1.1.3.7.1.6.1', '-OvQ');

                if (is_numeric($free)) {
                        discover_mempool($valid_mempool, $device, 0, 'dnos-mem', 'Memory Utilization', '1', null, null);
                }
        } else {
                $free = snmp_get($device, '.1.3.6.1.4.1.674.10895.5000.2.6132.1.1.1.1.4.1.0', '-OvQ');

                if (is_numeric($free)) {
                        discover_mempool($valid_mempool, $device, 0, 'dnos-mem', 'Memory Utilization', '1', null, null);
                }
        }
}
