<?php
  /*
   * LibreNMS module for Netonix
   *
   * Copyright (c) 2016 Tony Murray <murraytony@gmail.com>
   *
   * This program is free software: you can redistribute it and/or modify it
   * under the terms of the GNU General Public License as published by the
   * Free Software Foundation, either version 3 of the License, or (at your
   * option) any later version.  Please see LICENSE.txt at the top level of
   * the source code distribution for details.
   */

if ($device['os'] == 'netonix') {
    $total = snmp_get($device, "UCD-SNMP-MIB::memTotalReal.0", "-OvQU") * 1024;
    $free = snmp_get($device, "UCD-SNMP-MIB::memAvailReal.0", "-OvQU") * 1024;

    $mempool['total'] = $total;
    $mempool['free']  = $free;
    $mempool['used']  = $total - $free;
}
