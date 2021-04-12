<?php
/*
 * LibreNMS module to Graph Cisco IPSLA UDP Jitter metrics
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
$rrd_options .= ' -l 0 ';
$rrd_filename = Rrd::name($device['hostname'], ['sla', $sla['sla_nr'], 'jitter']);

if (Rrd::checkRrdExists($rrd_filename)) {
    $rrd_options .= " COMMENT:'                          Cur    Min    Max    Avg\\n'";

    $rrd_options .= ' DEF:SD=' . $rrd_filename . ':PacketLossSD:AVERAGE ';
    $rrd_options .= " LINE1.25:SD#FF3300:'Src to Dst (pkts)  ' ";
    $rrd_options .= " GPRINT:SD:LAST:'%5.2lf' ";
    $rrd_options .= " GPRINT:SD:MIN:'%5.2lf' ";
    $rrd_options .= " GPRINT:SD:MAX:'%5.2lf' ";
    $rrd_options .= " GPRINT:SD:AVERAGE:'%5.2lf'\\\l ";

    $rrd_options .= ' DEF:DS=' . $rrd_filename . ':PacketLossDS:AVERAGE ';
    $rrd_options .= " LINE1.25:DS#FF6633:'Dst to Src (pkts)  ' ";
    $rrd_options .= " GPRINT:DS:LAST:'%5.2lf' ";
    $rrd_options .= " GPRINT:DS:MIN:'%5.2lf' ";
    $rrd_options .= " GPRINT:DS:MAX:'%5.2lf' ";
    $rrd_options .= " GPRINT:DS:AVERAGE:'%5.2lf'\\\l ";
}
