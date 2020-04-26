<?php

if ($device['os'] == 'fortimail') {
    d_echo('Fortimail');
    $usage = snmp_get($device, '.1.3.6.1.4.1.12356.105.1.7.0', '-Ovq');
    if (is_numeric($usage)) {
        discover_mempool($valid_mempool, $device, '0', 'fortimail', 'Main Memory', '1', null, null);
    }
    
    $log = snmp_get($device, '.1.3.6.1.4.1.12356.105.1.8.0', '-Ovq');
    if (is_numeric($log)) {
        discover_mempool($valid_mempool, $device, '1', 'fortimail', 'Log Disk Usage', '1', null, null);
    }
	
	    $mail = snmp_get($device, '.1.3.6.1.4.1.12356.105.1.9.0', '-Ovq');
    if (is_numeric($mail)) {
        discover_mempool($valid_mempool, $device, '2', 'fortimail', 'Mailbox Disk Usage', '1', null, null);
    }
}