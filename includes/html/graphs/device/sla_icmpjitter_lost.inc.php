<?php
/*
 * LibreNMS module to Graph Cisco IPSLA ICMP Jitter metrics
 *
 * Copyright (c) 2016 Aaron Daniels <aaron@daniels.id.au>
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

$sla = dbFetchRow('SELECT `sla_nr` FROM `slas` WHERE `sla_id` = ?', [$vars['id']]);

require 'includes/html/graphs/common.inc.php';
$rrd_options .= ' -l 0 -E ';
$rrd_filename = Rrd::name($device['hostname'], ['sla', $sla['sla_nr'], 'icmpjitter']);

if (Rrd::checkRrdExists($rrd_filename)) {
    $rrd_options .= " COMMENT:'                          Cur  Min  Max  Avg\\n'";

    $rrd_options .= ' DEF:PL=' . $rrd_filename . ':PacketLoss:AVERAGE ';
    $rrd_options .= " LINE1.25:PL#008C00:'Packets Lost         ' ";
    $rrd_options .= ' GPRINT:PL:LAST:%3.0lf ';
    $rrd_options .= ' GPRINT:PL:MIN:%3.0lf ';
    $rrd_options .= ' GPRINT:PL:MAX:%3.0lf ';
    $rrd_options .= " GPRINT:PL:AVERAGE:'%3.0lf'\\\l ";

    $rrd_options .= ' DEF:PLA=' . $rrd_filename . ':PacketLateArrival:AVERAGE ';
    $rrd_options .= " LINE1.25:PLA#CC0000:'Late Arrival         ' ";
    $rrd_options .= ' GPRINT:PLA:LAST:%3.0lf ';
    $rrd_options .= ' GPRINT:PLA:MIN:%3.0lf ';
    $rrd_options .= ' GPRINT:PLA:MAX:%3.0lf ';
    $rrd_options .= " GPRINT:PLA:AVERAGE:'%3.0lf'\\\l ";
}
