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
$prefix    = rrd_name($device['hostname'], array($subtype, ''), '');
$filenames = glob($prefix."*.rrd");
$count     = count($filenames);
foreach ($filenames as $filename) {
    // find out what * expanded to
    $globpart = str_replace($prefix, '', $filename);    // take off the prefix
    $instance = substr($globpart, 0, -4);               // take off ".rrd"
    $ds             = array();
    $mibparts       = explode('-', $subtype);
    $mibvar         = end($mibparts);
    $ds['ds']       = 'mibval';
    $ds['descr']    = "$mibvar-$instance";
    $ds['filename'] = $filename;
    $rrd_list[]     = $ds;
}

$scale_min  = '0';
$simple_rrd = true;

// If there are multiple matching files, use a stacked graph instead of a line graph
if ($count > 1) {
    $nototal = 1;
    $divider = $count;
    $text_orig = 1;
    $colours = 'manycolours';
    include "includes/graphs/generic_multi_simplex_seperated.inc.php";
}
else {
    $colours = 'mixed';
    $nototal = 0;
    include "includes/graphs/generic_multi_line.inc.php";
}
