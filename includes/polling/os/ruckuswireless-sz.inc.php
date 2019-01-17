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

$productmib = trim($device['sysObjectID'], '" ');

$ruckusmodels    = array(
    "$productmib.5.0",
    '.1.3.6.1.4.1.25053.1.8.1.1.1.1.1.1.3.12.50.50.49.55.53.54.48.48.48.48.49.53',
);
$ruckusversions  = array(
    "$productmib.8.0",
    '.1.3.6.1.4.1.25053.1.8.1.1.1.1.1.1.9.12.50.50.49.55.53.54.48.48.48.48.49.53',
);
$ruckusserials   = array(
    "$productmib.7.0",
    '.1.3.6.1.4.1.25053.1.4.1.1.1.15.13.0',
);
$ruckuscountries = array(
    "$productmib.9.0",
    '.1.3.6.1.4.1.25053.1.8.1.1.1.1.3.1.4.139.32.129.213.150.98.64.217.163.219.42.60.244.221.227.247.247.122.136.22.48.73.64.205.132.132.130.145.146.117.221.195',
);

$hardware      = first_oid_match($device, $ruckusmodels);
$version       = first_oid_match($device, $ruckusversions);
$serial        = first_oid_match($device, $ruckusserials);
$ruckuscountry = first_oid_match($device, $ruckuscountries);
if (isset($ruckuscountry) && $ruckuscountry != '') {
    $version .= " ($ruckuscountry)";
}
