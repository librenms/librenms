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

$version = snmp_get($device, 'firmwareVersion.0', '-OQv', 'NETONIX-SWITCH-MIB', $config['mibdir'].':'.$config['mibdir'].'/netonix');
$version = str_replace('.n.........', '', $version); // version display bug in 1.3.9
$hardware = $poll_device['sysDescr'];
