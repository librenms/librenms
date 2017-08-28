<?php
/*
 *
 * LibreNMS processors discovery module for BDCom switches
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.

 * @package    LibreNMS
 * @subpackage discovery
 * @link       http://librenms.org
 * @copyright  2017 Carlos A. Pedreros Lizama
 * @author     Carlos A. Pedreros Lizama <carlos.pedreros@gmail.com>
 */

if ($device['os'] == 'bdcom') {
    echo 'BDCOM, NMS-PROCESS-MIB: ';

    $usage = snmp_get($device, 'nmspmCPUTotal5min.1', '-Ovq', 'NMS-PROCESS-MIB');

    if (is_numeric($usage['nmspmCPUTotal5min.1'])) {
        discover_processor($valid['processor'], $device, 'NMS-PROCESS-MIB::nmspmCPUTotal5min.1', '0', 'bdcom', 'CPU', '1', $usage, null, null);
    }
}

unset($processors_array);
