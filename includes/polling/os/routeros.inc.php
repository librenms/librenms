<?php

$version = trim(snmp_get($device, '1.3.6.1.4.1.14988.1.1.4.4.0', '-OQv', '', ''), '"');
if (strstr($poll_device['sysDescr'], 'RouterOS')) {
    $hardware = substr($poll_device['sysDescr'], 9);
}

$features = 'Level '.trim(snmp_get($device, '1.3.6.1.4.1.14988.1.1.4.3.0', '-OQv', '', ''), '"');
$serial = trim(snmp_get($device, '1.3.6.1.4.1.14988.1.1.7.3.0', '-OQv', '', ''), '"');
