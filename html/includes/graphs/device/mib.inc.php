<?php
/*
 * LibreNMS MIB-based polling
 *
 * Author: Paul Gear
 * Copyright (c) 2015 Gear Consulting Pty Ltd <github@libertysys.com.au>
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

$rrd_list = array();
$prefix = rrd_name($device['hostname'], array($subtype, ""), "");
foreach (glob($prefix."*.rrd") as $filename) {
    // find out what * expanded to
    $globpart = str_replace($prefix, '', $filename);    // take off the prefix
    $instance = substr($globpart, 0, -4);               // take off ".rrd"

    $ds = array();
    $mibparts = explode("-", $subtype);
    $mibvar = end($mibparts);
    $ds['ds'] = name_shorten($mibvar);
    $ds['descr'] = "$mibvar-$instance";
    $ds['filename'] = $filename;
    $rrd_list[] = $ds;
}

$colours    = 'mixed';
$scale_min  = "0";
$nototal    = 0;
$simple_rrd = true;

include("includes/graphs/generic_multi_line.inc.php");
