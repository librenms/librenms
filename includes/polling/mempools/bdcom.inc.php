<?php
/*
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.

 * @package    LibreNMS
 * @subpackage polling
 * @link       http://librenms.org
 * @copyright  2017 - Carlos A. Pedreros Lizama <carlos.pedreros@gmail.com>
 * @author     Carlos A. Pedreros Lizama <carlos.pedreros@gmail.com>

 .1.3.6.1.4.1.3320.9.48.1 nmsMemoryPoolUtilization.0 = [%][This is the memory pool utilization"]
 .1.3.6.1.4.1.3320.9.48.2 nmsMemoryPoolTotalMemorySize.0 = [Unsigned32]["This is the total memory size"]
 .1.3.6.1.4.1.3320.9.48.3 nmsMemoryPoolImageRatio.0 = [%]["This is the ratio of image size to total memory size"]
 .1.3.6.1.4.1.3320.9.48.4 nmsMemoryPoolRegionRatio.0 = [%]["This is the ratio of total region size to total memory size"]
 .1.3.6.1.4.1.3320.9.48.5 nmsMemoryPoolHeapRatio.0 = [%]["This is the ratio of heap size to total memory size"]
 .1.3.6.1.4.1.3320.9.48.6 nmsMemoryPoolHeapUtilization.0 = [%]["This is the heap utilization"]
 .1.3.6.1.4.1.3320.9.48.7 nmsMemoryPoolMessageBufferRatio.0 = [%]["This is the ratio of message buffer size to total memory size"]
 .1.3.6.1.4.1.3320.9.48.8 nmsMemoryPoolMessageBufferUtilization.0 = [%]["This is the message buffer of utilization"]
 .1.3.6.1.4.1.3320.9.48.9 nmsMemoryPoolTotalFlashSize.0 = [Unsigned32][nmsMemoryPoolTotalFlashSize]

 */

$total = snmp_get($device, '.1.3.6.1.4.1.3320.9.48.2.0', '-OvQ');
$used  = snmp_get($device, '.1.3.6.1.4.1.3320.9.48.1.0', '-OvQ');

echo 'BDCOM Memory Pool';

$mempool['total'] = snmp_get($device, '.1.3.6.1.4.1.3320.9.48.2.0', '-OvQ');
$mempool['perc']  = snmp_get($device, '.1.3.6.1.4.1.3320.9.48.1.0', '-OvQ');
$mempool['used']  = ($mempool['total'] / 100 * $mempool['perc']);
$mempool['free']  = ($mempool['total'] - $mempool['used']);
