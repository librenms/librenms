<?php
/**
 * Created by PhpStorm.
 * User: crc
 * Date: 8/29/16
 * Time: 2:55 AM
 */

if ($device['os'] == 'edgeswitch') {
    $total = snmp_get($device, '.1.3.6.1.4.1.4413.1.1.1.1.4.2.0', '-Oqv');
    $free = snmp_get($device, '.1.3.6.1.4.1.4413.1.1.1.1.4.1.0', '-Oqv');
    $mempool['total']   = $total;
    $mempool['free']    = $free;
    $mempool['used']    = $total - $free;
}