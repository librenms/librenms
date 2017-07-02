<?php

/*
 * LibreNMS Axos OS Polling module
 *
 * Copyright (c) 2016 Chris A. Evans <thecityofguanyu@outlook.com>
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */


// Device might not have a card 1 (or even card2 if it is an E7-20)
$version = strtok(snmp_walk($device, "e7CardSoftwareVersion.1", "-OQv", "E7-Calix-MIB"), PHP_EOL);
$hardware = "Axos " . $poll_device['sysDescr'];
$features = str_replace(PHP_EOL, ', ', snmp_walk($device, "e7CardProvType", "-OQv", "E7-Calix-MIB"));
$serial = str_replace(PHP_EOL, ', ', snmp_walk($device, "e7CardSerialNumber", "-OQv", "E7-Calix-MIB"));
