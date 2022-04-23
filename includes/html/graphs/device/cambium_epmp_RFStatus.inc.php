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
$rrdfilename = Rrd::name($device['hostname'], 'cambium-epmp-RFStatus');
if (Rrd::checkRrdExists($rrdfilename)) {
    $rrd_options .= " COMMENT:'dBm                Now       Ave      Max     \\n'";
    $rrd_options .= ' DEF:cambiumSTADLRSSI=' . $rrdfilename . ':cambiumSTADLRSSI:AVERAGE ';
    $rrd_options .= ' DEF:cambiumSTADLSNR=' . $rrdfilename . ':cambiumSTADLSNR:AVERAGE ';
    $rrd_options .= " AREA:cambiumSTADLRSSI#FF0000:'RSSI       ' ";
    $rrd_options .= ' GPRINT:cambiumSTADLRSSI:LAST:%0.2lf%s ';
    $rrd_options .= ' GPRINT:cambiumSTADLRSSI:MIN:%0.2lf%s ';
    $rrd_options .= ' GPRINT:cambiumSTADLRSSI:MAX:%0.2lf%s\\\l ';
    $rrd_options .= " AREA:cambiumSTADLSNR#0000FF:'SNR     ' ";
    $rrd_options .= ' GPRINT:cambiumSTADLSNR:LAST:%0.2lf%s ';
    $rrd_options .= ' GPRINT:cambiumSTADLSNR:MIN:%0.2lf%s ';
    $rrd_options .= ' GPRINT:cambiumSTADLSNR:MAX:%0.2lf%s\\\l ';
}
