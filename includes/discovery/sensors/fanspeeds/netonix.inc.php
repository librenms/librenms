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

// Netonix Fan Speeds
if ($device['os'] == 'netonix') {
    echo 'Netonix: ';
    $oids = snmpwalk_cache_multi_oid($device, 'fanTable', array(), 'NETONIX-SWITCH-MIB', '+'.$config['mibdir'].'/netonix');
    if (is_array($oids)) {
        foreach ($oids as $index => $entry) {
            if (is_numeric($entry['fanSpeed']) && is_numeric($index)) {
                $descr   = "Fan ".$index;
                $oid     = '.1.3.6.1.4.1.46242.2.1.2.'.$index;
                $current = $entry['fanSpeed'];
                discover_sensor($valid['sensor'], 'fanspeed', $device, $oid, $index, $device['os'], $descr, '1', '1', '0', '0', null, null, $current);
            }
        }
    }
}//end if
