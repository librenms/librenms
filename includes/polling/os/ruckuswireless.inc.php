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

$productmib = trim(snmp_get($device, '.1.3.6.1.2.1.1.2.0', '-Oqv'), '" ');

$ruckusmodels    = array(
    "$productmib.5.0",
    '.1.3.6.1.4.1.25053.1.2.1.1.1.1.9.0',
    '.1.3.6.1.4.1.25053.1.1.2.1.1.1.1.0',
    '.1.3.6.1.2.1.1.1.0',
);
$ruckusversions  = array(
    "$productmib.8.0",
    '.1.3.6.1.4.1.25053.1.1.3.1.1.1.1.1.3.1',
);
$ruckusserials   = array(
    "$productmib.7.0",
    '.1.3.6.1.4.1.25053.1.1.2.1.1.1.2.0',
);
$ruckuscountries = array(
    "$productmib.9.0",
    '.1.3.6.1.4.1.25053.1.2.1.1.1.1.20',
);

$hardware      = first_oid_match($device, $ruckusmodels);
$version       = first_oid_match($device, $ruckusversions);
$serial        = first_oid_match($device, $ruckusserials);
$ruckuscountry = first_oid_match($device, $ruckuscountries);
if (isset($ruckuscountry) && $ruckuscountry != '') {
    $version .= " ($ruckuscountry)";
}
