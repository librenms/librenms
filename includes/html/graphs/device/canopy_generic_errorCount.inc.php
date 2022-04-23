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
$rrdfilename = Rrd::name($device['hostname'], 'canopy-generic-errorCount');
if (Rrd::checkRrdExists($rrdfilename)) {
    $rrd_options .= " COMMENT:'dBm                Now       Ave      Max     \\n'";
    $rrd_options .= ' DEF:fecInErrorsCount=' . $rrdfilename . ':fecInErrorsCount:AVERAGE ';
    $rrd_options .= ' DEF:fecOutErrorsCount=' . $rrdfilename . ':fecOutErrorsCount:AVERAGE ';
    $rrd_options .= " LINE2:fecInErrorsCount#FF0000:'In Error Count        ' ";
    $rrd_options .= ' GPRINT:fecInErrorsCount:LAST:%0.2lf%s ';
    $rrd_options .= ' GPRINT:fecInErrorsCount:MIN:%0.2lf%s ';
    $rrd_options .= ' GPRINT:fecInErrorsCount:MAX:%0.2lf%s\\\l ';
    $rrd_options .= " LINE2:fecOutErrorsCount#00FF00:'Out Error Count       ' ";
    $rrd_options .= ' GPRINT:fecOutErrorsCount:LAST:%0.2lf%s ';
    $rrd_options .= ' GPRINT:fecOutErrorsCount:MIN:%0.2lf%s ';
    $rrd_options .= ' GPRINT:fecOutErrorsCount:MAX:%0.2lf%s\\\l ';
}
