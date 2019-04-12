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
$rrdfilename = rrd_name($device['hostname'], 'cisco-wwan-rssi');
if (rrdtool_check_rrd_exists($rrdfilename)) {
    $rrd_options .= " COMMENT:'dBm              Now       Ave      Max     \\n'";
    $rrd_options .= ' DEF:rssi='.$rrdfilename.':rssi:AVERAGE ';
    $rrd_options .= ' --alt-autoscale';
    $rrd_options .= ' --lower-limit=-110 ';
    $rrd_options .= " --vertical-label='dBm'";
    $rrd_options .= " LINE2:rssi#FF0000:'RSSI       ' ";
    $rrd_options .= ' GPRINT:rssi:LAST:%0.2lf%s ';
    $rrd_options .= ' GPRINT:rssi:MIN:%0.2lf%s ';
    $rrd_options .= ' GPRINT:rssi:MAX:%0.2lf%s\\\l ';
}
