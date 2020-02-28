<?php
/*
 * forcepoint-os.inc.php
 * LibreNMS mempool poller module for forcepoint
 */
if ($device['os'] == 'forcepoint-os') {
    echo 'FORCEPOINT Mempools:';
    $memory_total = snmp_get($device, '.1.3.6.1.4.1.1369.5.2.1.11.2.4.0', '-OvQU');
    $memory_used = snmp_get($device, '.1.3.6.1.4.1.1369.5.2.1.11.2.5.0', '-OvQU');
    $memory_free = snmp_get($device, '.1.3.6.1.4.1.1369.5.2.1.11.2.6.0', '-OvQU');
    $mempool['total'] = $memory_total;
    $mempool['free'] = $memory_free;
    $mempool['used'] = $memory_used;
}
