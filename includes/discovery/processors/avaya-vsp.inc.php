<?php

if ($device['os'] == 'avaya-vsp') {
    $oid = '.1.3.6.1.4.1.2272.1.85.10.1.1.2.1';
    $usage = snmp_walk($device, $oid, '-Ovq');
    discover_processor($valid['processor'], $device, $oid, 1, 'avaya-vsp', 'VSP Processor', '1', $usage);
}
