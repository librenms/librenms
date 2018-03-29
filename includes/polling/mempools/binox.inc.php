<?php

/*
 * LibreNMS Telco Systems RAM Polling module
 *
 * Copyright (c) 2016 Chris A. Evans <thecityofguanyu@outlook.com>
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */


// Somewhat of an ugly hack since Telco Systems device
// don't support fetching total memory of the device over SNMP. Only used percentage.
// Given OID returns usage in percent so we set total to 100 in order to get a proper graph

$mempool['total']       = "100";
$usage                  = snmp_get($device, ".1.3.6.1.4.1.738.10.111.3.1.3.0", "-Ovq");
$usage                  = str_replace('%', '', $usage);
$usage                  = str_replace('"', '', $usage);
$mempool['used']        = $usage;
$mempool['free']        = ($mempool['total'] - $mempool['used']);
