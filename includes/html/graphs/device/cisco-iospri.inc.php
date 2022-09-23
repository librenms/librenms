<?php

  /*
   * LibreNMS module to Graph Primary Rate ISDN Resources in a Cisco Voice Router
   *
   * Copyright (c) 2015 Aaron Daniels <aaron@daniels.id.au>
   *
   * This program is free software: you can redistribute it and/or modify it
   * under the terms of the GNU General Public License as published by the
   * Free Software Foundation, either version 3 of the License, or (at your
   * option) any later version.  Please see LICENSE.txt at the top level of
   * the source code distribution for details.
   */

include 'includes/html/graphs/common.inc.php';
$rrd_options .= ' -l 0 -E ';
$rrd_filename = Rrd::name($device['hostname'], 'cisco-iospri');

if (Rrd::checkRrdExists($rrd_filename)) {
    $rrd_options .= " COMMENT:'                            Cur   Min  Max\\n'";
    $rrd_options .= ' DEF:Total=' . $rrd_filename . ':total:AVERAGE ';
    $rrd_options .= ' AREA:Total#c099ff ';
    $rrd_options .= " LINE1.25:Total#0000ee:'PRI Channels total      ' ";
    $rrd_options .= ' GPRINT:Total:LAST:%3.0lf ';
    $rrd_options .= ' GPRINT:Total:MIN:%3.0lf ';
    $rrd_options .= " GPRINT:Total:MAX:%3.0lf\l ";

    $rrd_options .= ' DEF:Active=' . $rrd_filename . ':active:AVERAGE ';
    $rrd_options .= ' AREA:Active#aaff99 ';
    $rrd_options .= " LINE1.25:Active#00ee00:'PRI Channels in use     ' ";
    $rrd_options .= ' GPRINT:Active:LAST:%3.0lf ';
    $rrd_options .= ' GPRINT:Active:MIN:%3.0lf ';
    $rrd_options .= " GPRINT:Active:MAX:%3.0lf\l ";
}
