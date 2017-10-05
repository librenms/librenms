<?php

if ($mempool['mempool_index'] == 0) {
    $mem_free = snmp_get($device, '.1.3.6.1.4.1.232.11.2.13.2.0', '-OvQ');
    $mem_capacity = snmp_get($device, '.1.3.6.1.4.1.232.11.2.13.1.0', '-OvQ');
    //$mem_capacity = ($mem_capacity*1024*1024);
    $mempool['total'] = $mem_capacity*1024*1024;
    $mempool['free']  = $mem_free*1024*1024;
    $mempool['used']  = $mempool['total'] - $mempool['free'];
}

if ($mempool['mempool_index'] == 1) {
    $page_free = snmp_get($device, '.1.3.6.1.4.1.232.11.2.13.4.0', '-OvQ');
    $page_capacity = snmp_get($device, '.1.3.6.1.4.1.232.11.2.13.3.0', '-OvQ');
    //$swap_capacity = ($swap_capacity*1024*1024);
    $mempool['total'] = $page_capacity*1024*1024;
    $mempool['free']  = $page_free*1024*1024;
    $mempool['used']  = $mempool['total'] - $mempool['free'];
}

unset(
    $mem_free,
    $mem_capacity,
    $page_free,
    $page_capacity
);
