<?php

echo 'Checkpoint SecurePlatform CPU Usage';

$usage = snmp_get($device, '.1.3.6.1.4.1.2620.1.6.7.2.4.0', '-OvQ', 'CHECKPOINT-MIB');

if (is_numeric($usage)) {
        $proc = $usage;
}
