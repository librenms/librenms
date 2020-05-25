<?php
/*
 * LibreNMS Ruckus Wireless OS information module
 *
 * Originally by:
 * Copyright (c) 2015 Søren Friis Rosiak <sorenrosiak@gmail.com>
 *
 * Updates by Paul Gear:
 * Copyright (c) 2015 Gear Consulting Pty Ltd <github@libertysys.com.au>
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

$hardware      = snmp_getnext($device, '.1.3.6.1.4.1.25053.1.8.1.1.1.1.1.1.3', "-OQv");
$version       = snmp_getnext($device, '.1.3.6.1.4.1.25053.1.8.1.1.1.1.1.1.9', "-OQv");
$serial        = snmp_get($device, '.1.3.6.1.4.1.25053.1.4.1.1.1.15.13.0', "-OQv");
$features      = "Licenses: " . snmp_get($device, '.1.3.6.1.4.1.25053.1.4.1.1.1.15.1.0', "-OQv") . "/" . snmp_getnext($device, '.1.3.6.1.4.1.25053.1.8.1.1.1.1.1.1.10', "-OQv");

$ruckuscountry = snmp_getnext($device, '.1.3.6.1.4.1.25053.1.8.1.1.1.1.3.1.4', "-OQv");
if (isset($ruckuscountry) && $ruckuscountry != '') {
    $version .= " ($ruckuscountry)";
}
