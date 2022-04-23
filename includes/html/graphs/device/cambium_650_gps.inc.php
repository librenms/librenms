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

$rrdfilename = Rrd::name($device['hostname'], 'cambium-650-gps');
if (Rrd::checkRrdExists($rrdfilename)) {
    $rrd_options .= " COMMENT:'\\n'";
    $rrd_options .= ' DEF:gps=' . $rrdfilename . ':gps:AVERAGE ';
    $rrd_options .= " LINE2:gps#9B30FF:'GPS Status' ";
    $rrd_options .= ' GPRINT:gps:LAST:%0.2lf%s ';
    $rrd_options .= ' GPRINT:gps:MIN:%0.2lf%s ';
    $rrd_options .= ' GPRINT:gps:MAX:%0.2lf%s\\\l ';
    $rrd_options .= " COMMENT:'0 = Locked\\n' ";
    $rrd_options .= " COMMENT:'1 = Holdover\\n' ";
    $rrd_options .= " COMMENT:'2 = holdoverNoGPSSyncIn\\n' ";
    $rrd_options .= " COMMENT:'3 = notSynchronized\\n' ";
    $rrd_options .= " COMMENT:'4 = notSynchronizedNoGPSSyncIn\\n' ";
    $rrd_options .= " COMMENT:'5 = pTPSYNCNotConnected\\n' ";
    $rrd_options .= " COMMENT:'6 = initialising\\n' ";
    $rrd_options .= " COMMENT:'7 = clusterTimingMaster\\n' ";
    $rrd_options .= " COMMENT:'8 = acquiringLock\\n' ";
    $rrd_options .= " COMMENT:'9 = inactive\\n' ";
}
