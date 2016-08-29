<?php
/**
 * Created by PhpStorm.
 * User: crc
 * Date: 8/29/16
 * Time: 2:39 AM
 */

d_echo('EdgeSwitch CPU usage:');
if ($device['os'] == 'edgeswitch') {
    $proc_usage = snmp_get($device, '.1.3.6.1.4.1.4413.1.1.1.1.4.9.0', '-Ovq');
    preg_match('/([0-9]+.[0-9]+)/',$proc_usage,$usage);
    $proc = $usage[0];
}
