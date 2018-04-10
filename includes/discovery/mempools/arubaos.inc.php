<?php

/*
 * LibreNMS mempools discovery module for ArubaOS controllers
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 * 
 * @package    LibreNMS
 * @subpackage discovery
 * @link       http://librenms.org
 * @copyright  2018 Patrick Ryon (Slashdoom)
 * @author     Patrick Ryon (Slashdoom) <patrick@slashdoom.com>
 */

 /*
  * WLSX-SWITCH-MIB::sysXMemorySize.1 = INTEGER: 5184256
  * WLSX-SWITCH-MIB::sysXMemoryUsed.1 = INTEGER: 4265280
  * WLSX-SWITCH-MIB::sysXMemoryFree.1 = INTEGER: 918976
  */

if ($device['os'] === 'arubaos') {
    echo 'ARUBAOS-MEMORY-POOL: ';

    $total = snmp_get($device, 'sysXMemorySize.1', '-OvQ', 'WLSX-SWITCH-MIB');
    $used  = snmp_get($device, 'sysXMemoryUsed.1', '-OvQ', 'WLSX-SWITCH-MIB');
    $free  = snmp_get($device, 'sysXMemoryFree.1', '-OvQ', 'WLSX-SWITCH-MIB');
    $perc  = ($used / $total * 100);

    if (is_numeric($total) && is_numeric($used)) {
        discover_mempool($valid_mempool, $device, 0, 'arubaos', 'Memory', '1', null, null);
    }
}
