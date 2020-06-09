<?php
/*
 * LibreNMS
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

require 'includes/html/graphs/common.inc.php';
$rrdfilename = rrd_name($device['hostname'], 'cisco-wwan-mnc');
if (rrdtool_check_rrd_exists($rrdfilename)) {
    $rrd_options .= ' DEF:mnc='.$rrdfilename.':mnc:LAST ';
    $rrd_options .= ' --lower-limit 0 ';
    $rrd_options .= " --vertical-label='MNC'";
    $rrd_options .= " LINE2:mnc#750F7DFF:'MNC Mobile Netwok Code'";
    $rrd_options .= ' GPRINT:mnc:LAST:%0.2lf%s\\\l ';
}
