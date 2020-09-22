<?php
/*
 *
 * LibreNMS mempools polling module for opengear devices
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.

 * @package    LibreNMS
 * @subpackage polling
 * @link       http://librenms.org
 */

$mempool['total'] = intval(preg_replace('/[^0-9]+/', '', snmp_get($device, 'memTotalReal.0', '-OQUs', 'UCD-SNMP-MIB')), 10);
$mempool['free'] = intval(preg_replace('/[^0-9]+/', '', snmp_get($device, 'memTotalFree.0', '-OQUs', 'UCD-SNMP-MIB')), 10);
$mempool['used'] = ($mempool['total'] - $mempool['free']);
$mempool['perc'] = ($mempool['used'] / $mempool['total']) * 100;
