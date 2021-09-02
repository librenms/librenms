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

$ruckus_data = snmp_get_multi_oid($device, ['.1.3.6.1.4.1.25053.1.2.1.1.1.1.18.0', '.1.3.6.1.4.1.25053.1.2.1.1.1.1.15.0', '.1.3.6.1.4.1.25053.1.2.1.1.1.1.12.0', '.1.3.6.1.4.1.25053.1.2.1.1.1.1.9.0', '.1.3.6.1.4.1.25053.1.2.1.1.1.1.20.0', '.1.3.6.1.4.1.25053.1.2.1.1.1.15.15.0']);

$version = $ruckus_data['.1.3.6.1.4.1.25053.1.2.1.1.1.1.18.0'];
$serial = $ruckus_data['.1.3.6.1.4.1.25053.1.2.1.1.1.1.15.0'];
$features = 'Licenses: ' . $ruckus_data['.1.3.6.1.4.1.25053.1.2.1.1.1.15.15.0'] . '/' . $ruckus_data['.1.3.6.1.4.1.25053.1.2.1.1.1.1.12.0'];
$hardware = $ruckus_data['.1.3.6.1.4.1.25053.1.2.1.1.1.1.9.0'];
$ruckuscountry = $ruckus_data['.1.3.6.1.4.1.25053.1.2.1.1.1.1.20.0'];

if (isset($ruckuscountry) && $ruckuscountry != '') {
    $version .= " ($ruckuscountry)";
}
