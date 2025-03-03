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
$rrdfilename = Rrd::name($device['hostname'], 'cambium-epmp-access');
if (Rrd::checkRrdExists($rrdfilename)) {
    $rrd_options .= " COMMENT:'Value                Now     \\n'";
    $rrd_options .= ' DEF:entryAttempt=' . $rrdfilename . ':entryAttempt:AVERAGE ';
    $rrd_options .= ' DEF:entryAccess=' . $rrdfilename . ':entryAccess:AVERAGE ';
    $rrd_options .= ' DEF:authFailure=' . $rrdfilename . ':authFailure:AVERAGE ';
    $rrd_options .= " LINE2:entryAttempt#FFF000:'Entry Attempts       ' ";
    $rrd_options .= ' GPRINT:entryAttempt:LAST:%0.2lf%s\\\l ';
    $rrd_options .= " LINE2:entryAccess#00FF00:'Entry Access       ' ";
    $rrd_options .= ' GPRINT:entryAccess:LAST:%0.2lf%s\\\l  ';
    $rrd_options .= " LINE2:authFailure#FF0000:'Auth Failure       ' ";
    $rrd_options .= ' GPRINT:authFailure:LAST:%0.2lf%s\\\l  ';
}
