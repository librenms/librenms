<?php

echo 'Dlink AP CPU Usage';

$processor_oid=$device['sysObjectID'].'.5.1.3.0';
$usage = snmp_get($device, $processor_oid, '-OvQ', '');

if (is_numeric($usage)) {
    $proc = $usage;
}
