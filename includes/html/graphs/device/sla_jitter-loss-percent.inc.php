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
use App\Models\Sla;

$sla_nr = Sla::where('sla_id', $vars['id'])->value('sla_nr');

if ($sla_nr) {
    require 'includes/html/graphs/common.inc.php';
    $rrd_options .= ' -l -100 -u 100 -E -r';
    $rrd_filename_1 = Rrd::name($device['hostname'], ['sla', $sla_nr, 'jitter']);
    $rrd_filename_2 = Rrd::name($device['hostname'], ['sla', $sla_nr, 'NumPackets']);

    if (Rrd::checkRrdExists($rrd_filename)) {
        $rrd_options .= " COMMENT:'                      Cur    Min    Max    Avg\\n'";

        $rrd_options .= ' DEF:PacketLossSD=' . $rrd_filename_1 . ':PacketLossSD:AVERAGE ';
        $rrd_options .= ' DEF:PacketLossDS=' . $rrd_filename_1 . ':PacketLossDS:AVERAGE ';
        $rrd_options .= ' DEF:NumPackets=' . $rrd_filename_2 . ':NumPackets:AVERAGE ';

        $rrd_options .= ' CDEF:PktLossOut=PacketLossSD,NumPackets,/,100,* ';
        $rrd_options .= " LINE1.25:PktLossOut#0000ee:'Src to Dst (%)  ' ";
        $rrd_options .= " GPRINT:PktLossOut:LAST:'%5.2lf' ";
        $rrd_options .= " GPRINT:PktLossOut:MIN:'%5.2lf' ";
        $rrd_options .= " GPRINT:PktLossOut:MAX:'%5.2lf' ";
        $rrd_options .= " GPRINT:PktLossOut:AVERAGE:'%5.2lf'\\\l ";

        $rrd_options .= ' CDEF:PktLossIn=PacketLossDS,NumPackets,/,100,*,-1,* ';
        $rrd_options .= " LINE1.25:PktLossIn#008C00:'Dst to Src (%)  ' ";
        $rrd_options .= " GPRINT:PktLossIn:LAST:'%5.2lf' ";
        $rrd_options .= " GPRINT:PktLossIn:MIN:'%5.2lf' ";
        $rrd_options .= " GPRINT:PktLossIn:MAX:'%5.2lf' ";
        $rrd_options .= " GPRINT:PktLossIn:AVERAGE:'%5.2lf'\\\l ";
    }
}
