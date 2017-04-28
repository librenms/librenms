<?php
/*
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.

 * @package    LibreNMS
 * @subpackage discovery
 * @link       http://librenms.org
 * @copyright  2017 - Carlos A. Pedreros Lizama <carlos.pedreros@gmail.com>
 * @author     Carlos A. Pedreros Lizama <carlos.pedreros@gmail.com>

 .1.3.6.1.4.1.3320.9.109.1.1.1.1.1.1 nmspmCPUTotalIndex.1 = [Unsigned32][Index]
 .1.3.6.1.4.1.3320.9.109.1.1.1.1.2.1 nmspmCPUTotalPhysicalIndex.1 = iso.0 [oid]
 .1.3.6.1.4.1.3320.9.109.1.1.1.1.3.1 nmspmCPUTotal5sec.1 = [%]["The overall CPU busy percentage in the last 5 second period. This object is deprecated by nmspmCPUTotal5secRev which has the changed range of value (0..100)."]
 .1.3.6.1.4.1.3320.9.109.1.1.1.1.4.1 nmspmCPUTotal1min.1 = [%]["The overall CPU busy percentage in the last 1 minute period. This object is deprecated by nmspmCPUTotal1minRev which has the changed range of value (0..100)."]
 .1.3.6.1.4.1.3320.9.109.1.1.1.1.5.1 nmspmCPUTotal5min.1 = [%]["The overall CPU busy percentage in the last 5 minute period. This object is deprecated by nmspmCPUTotal5minRev which has the changed range of value (0..100)."]
 .1.3.6.1.4.1.3320.9.109.1.1.2.0 nmspmCPUMaxUtilization.0 = [%]["The max value of nmspmCPUTotal5sec."]
 .1.3.6.1.4.1.3320.9.109.1.1.3.0 nmspmCPUClearMaxUtilization.0 = [%]["To clear nmspmCPUMaxUtilization."]
 .1.3.6.1.4.1.3320.9.109.1.1.4.0 nmspmCPU.4.0 = 80 [?][?]

 */

if ($device['os'] == 'bdcom') {

    echo 'BDCOM, NMS-PROCESS-MIB';

    $usage = snmp_get($device, '.1.3.6.1.4.1.3320.9.109.1.1.1.1.5.1', '-Ovqn');

    if (is_numeric($usage)) {
        discover_processor($valid['processor'], $device, '.1.3.6.1.4.1.3320.9.109.1.1.1.1.5.1', '0', 'bdcom', 'CPU, '1', $usage, null, null);
    }
}

unset($processors_array);
