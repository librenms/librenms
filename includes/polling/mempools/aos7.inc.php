<?php

$mempool['units'] = '1';

$mempool['total'] = snmp_get($device, '.1.3.6.1.4.1.6486.801.1.1.1.2.1.1.3.4.0', '-OvQ', 'ALCATEL-IND1-SYSTEM-MIB', 'nokia/aos7');
$mempool['total'] *= 1024; // Memory in MB
$percent = snmp_get($device, '.1.3.6.1.4.1.6486.801.1.2.1.16.1.1.1.1.1.8.0', '-OvQ', 'ALCATEL-IND1-HEALTH-MIB', 'nokia/aos7');

$mempool['used'] = ($mempool['total'] * ($percent / 100));
$mempool['free'] = ($mempool['total'] - $mempool['used']);
