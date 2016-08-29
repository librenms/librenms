<?php
/**
 * Created by PhpStorm.
 * User: crc
 * Date: 8/29/16
 * Time: 1:53 AM
 */

if ($device['os'] == 'edgeswitch') {
    //SNMPv2-SMI::enterprises.4413.1.1.1.1.4.9.0
    d_echo('EdgeSwitch CPU usage:');
    $descr = 'Processor';
    $proc_usage = snmp_get($device, '.1.3.6.1.4.1.4413.1.1.1.1.4.9.0', '-Ovq');
    preg_match('/([0-9]+.[0-9]+)/',$proc_usage,$usage);
    if (is_numeric($usage[0])) {
        discover_processor($valid['processor'], $device, '.1.3.6.1.4.1.4413.1.1.1.1.4.9.0', '0', 'edgeswitch', $descr, '1', $usage[0], null, null);
    }
}
