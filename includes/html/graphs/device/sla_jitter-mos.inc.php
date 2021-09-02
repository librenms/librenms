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
$rrd_options .= ' -l 0 -E ';
$rrd_filename = Rrd::name($device['hostname'], ['sla', $sla['sla_nr'], 'jitter']);

if (Rrd::checkRrdExists($rrd_filename)) {
    $rrd_options .= " COMMENT:'                           Cur   Min   Max   Avg\\n'";

    $rrd_options .= ' DEF:MOS=' . $rrd_filename . ':MOS:AVERAGE ';
    $rrd_options .= " LINE1.25:MOS#0000ee:'Mean Opinion Score   ' ";
    $rrd_options .= ' GPRINT:MOS:LAST:%3.2lf ';
    $rrd_options .= ' GPRINT:MOS:MIN:%3.2lf ';
    $rrd_options .= ' GPRINT:MOS:MAX:%3.2lf ';
    $rrd_options .= " GPRINT:MOS:AVERAGE:'%3.2lf'\\\l ";
}
