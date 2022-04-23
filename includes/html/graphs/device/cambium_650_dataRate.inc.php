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

$rrdfilename = Rrd::name($device['hostname'], 'cambium-650-dataRate');
if (Rrd::checkRrdExists($rrdfilename)) {
    $rrd_options .= " COMMENT:'Mbps                        Now       Ave      Max     \\n'";
    $rrd_options .= ' DEF:receiveDataRate=' . $rrdfilename . ':receiveDataRate:AVERAGE ';
    $rrd_options .= ' DEF:transmitDataRate=' . $rrdfilename . ':transmitDataRate:AVERAGE ';
    $rrd_options .= ' DEF:aggregateDataRate=' . $rrdfilename . ':aggregateDataRate:AVERAGE ';
    $rrd_options .= " LINE2:receiveDataRate#0000FF:'Receive Data Rate         ' ";
    $rrd_options .= ' GPRINT:receiveDataRate:LAST:%0.2lf%s ';
    $rrd_options .= ' GPRINT:receiveDataRate:MIN:%0.2lf%s ';
    $rrd_options .= ' GPRINT:receiveDataRate:MAX:%0.2lf%s\\\l ';
    $rrd_options .= " LINE2:transmitDataRate#FF0000:'Transmit Data Rate        ' ";
    $rrd_options .= ' GPRINT:transmitDataRate:LAST:%0.2lf%s ';
    $rrd_options .= ' GPRINT:transmitDataRate:MIN:%0.2lf%s ';
    $rrd_options .= ' GPRINT:transmitDataRate:MAX:%0.2lf%s\\\l ';
    $rrd_options .= " LINE2:aggregateDataRate#FFF000:'Aggregate Data Rate        ' ";
    $rrd_options .= ' GPRINT:aggregateDataRate:LAST:%0.2lf%s ';
    $rrd_options .= ' GPRINT:aggregateDataRate:MIN:%0.2lf%s ';
    $rrd_options .= ' GPRINT:aggregateDataRate:MAX:%0.2lf%s\\\l ';
}
