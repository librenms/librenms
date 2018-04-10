<?php

/*
 * LibreNMS mempools polling module for ArubaOS controllers
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

  echo 'ARUBAOS-MEMORY-POOL: ';

  $memory_pool = snmp_get_multi_oid($device, 'sysXMemorySize.1 sysXMemoryUsed.1 sysXMemoryFree.1', '-OQUs', 'WLSX-SWITCH-MIB');

  $mempool['total'] = $memory_pool['sysXMemorySize.1'];
  $mempool['used']  = $memory_pool['sysXMemoryUsed.1'];
  $mempool['free']  = $memory_pool['sysXMemoryFree.1'];
  $mempool['perc']  = ($mempool['used'] / $mempool['total'] * 100);
    