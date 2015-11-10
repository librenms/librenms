<?php
/*
 * LibreNMS Pulse Secure OS information module
 *
 * Copyright (c) 2015 Christophe Martinet Chrisgfx <martinet.christophe@gmail.com>
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */
// Simple hard-coded poller for Pulse Secure
// Yes, it really can be this simple.

echo 'Pulse Secure MemPool'.'\n';

if ($device['os'] == 'pulse') {
  $perc     = str_replace('"', "", snmp_get($device, "PULSESECURE-PSG-MIB::iveMemoryUtil.0", '-OvQ'));
  $memory_available = str_replace('"', "", snmp_get($device, "UCD-SNMP-MIB::memTotalReal.0", '-OvQ'));
  $mempool['total'] = $memory_available;

  if (is_numeric($perc)) {
    $mempool['used'] = ($memory_available / 100 * $perc);
    $mempool['free'] = ($memory_available - $mempool['used']);
  }

  echo "PERC " .$perc."%\n";
  echo "Avail " .$mempool['total']."\n";

}
