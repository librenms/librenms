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

$rrd_filename = Rrd::name($device['hostname'], 'cambium-epmp-traffic');

if (Rrd::checkRrdExists($rrd_filename)) {
    $graph_params->vertical_label = 'bits/sec';
    $graph_params->scale_min = 0;

    $rrd_options[] = "DEF:rxBytes=$rrd_filename:rxBytes:AVERAGE";
    $rrd_options[] = "DEF:txBytes=$rrd_filename:txBytes:AVERAGE";
    $rrd_options[] = 'CDEF:rxBits=rxBytes,8,*';
    $rrd_options[] = 'CDEF:txBits=txBytes,8,*';
    $rrd_options[] = 'LINE2:rxBits#00AA00:RX Traffic';
    $rrd_options[] = 'GPRINT:rxBits:LAST:Last\\:%8.2lf %s';
    $rrd_options[] = 'GPRINT:rxBits:AVERAGE:Avg\\:%8.2lf %s';
    $rrd_options[] = 'GPRINT:rxBits:MAX:Max\\:%8.2lf %s\\n';
    $rrd_options[] = 'LINE2:txBits#0000AA:TX Traffic';
    $rrd_options[] = 'GPRINT:txBits:LAST:Last\\:%8.2lf %s';
    $rrd_options[] = 'GPRINT:txBits:AVERAGE:Avg\\:%8.2lf %s';
    $rrd_options[] = 'GPRINT:txBits:MAX:Max\\:%8.2lf %s\\n';
}
