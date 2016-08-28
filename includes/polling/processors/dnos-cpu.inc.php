<?php
echo 'DNOS CPU Usage';

if ($device['os'] == 'dnos') {
    preg_match('/(\d*\.\d*)/', snmp_get($device, '.1.3.6.1.4.1.674.10895.5000.2.6132.1.1.1.1.4.9.0', '-OvQ'), $matches);
    $proc = $matches[0];
}
