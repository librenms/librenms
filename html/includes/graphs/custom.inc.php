<?php
/*
 * LibreNMS custom graphing
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

// FIXME: Dummy implementation which only supports ruckuswireless processor & mempool

$i = 0;
$rrd_list = array();
if ($subtype == 'processor') {
    $rrd_list[0] = array(
        'area'     => 1,
        'ds'       => 'mibval',
        'descr'    => 'CPU Utilization',
        'filename' => rrd_name($device['hostname'], array('ruckusZDSystemStats-CPUUtil-0')),
    );
}

if ($subtype == 'mempool') {
    $rrd_list[0] = array(
        'area'     => 1,
        'ds'       => 'mibval',
        'descr'    => 'Memory Utilization',
        'filename' => rrd_name($device['hostname'], array('ruckusZDSystemStats-MemoryUtil-0')),
    );
}

$units     = '%%';
$colours   = 'mixed';
$scale_min = '0';
$scale_max = '100';
$nototal   = 1;

require_once 'includes/graphs/generic_multi_line.inc.php';
