<?php

  /*
   * LibreNMS module to Graph Hardware MTP Resources in a Cisco Voice Router
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
$rrd_filename = Rrd::name($device['hostname'], 'cisco-voice-ip');
if (Rrd::checkRrdExists($rrd_filename)) {
    $rrd_options .= " COMMENT:'                             Cur  Min  Max\\n'";
    $rrd_options .= ' DEF:sip=' . $rrd_filename . ':sip:AVERAGE ';
    $rrd_options .= ' DEF:sip_max=' . $rrd_filename . ':sip:MAX';
    $rrd_options .= ' AREA:sip_max#c099ff77 ';
    $rrd_options .= " LINE1.25:sip#0000ee:'sip active calls        ' ";
    $rrd_options .= ' GPRINT:sip:LAST:%3.0lf ';
    $rrd_options .= ' GPRINT:sip:MIN:%3.0lf ';
    $rrd_options .= " GPRINT:sip_max:MAX:%3.0lf\\\l ";

    $rrd_options .= ' DEF:h323=' . $rrd_filename . ':h323:AVERAGE ';
    $rrd_options .= ' DEF:h323_max=' . $rrd_filename . ':h323:MAX';
    $rrd_options .= ' AREA:h323_max#aaff99dd ';
    $rrd_options .= " LINE1.25:h323#00ee00:'h323 active calls       ' ";
    $rrd_options .= ' GPRINT:h323:LAST:%3.0lf ';
    $rrd_options .= ' GPRINT:h323:MIN:%3.0lf ';
    $rrd_options .= " GPRINT:h323_max:MAX:%3.0lf\\\l ";

    $rrd_options .= ' DEF:mgcp=' . $rrd_filename . ':mgcp:AVERAGE ';
    $rrd_options .= ' DEF:mgcp_max=' . $rrd_filename . ':mgcp:MAX';
    $rrd_options .= ' AREA:mgcp_max#ffaa99dd ';
    $rrd_options .= " LINE1.25:mgcp#ee0000:'mgcp active calls       ' ";
    $rrd_options .= ' GPRINT:mgcp:LAST:%3.0lf ';
    $rrd_options .= ' GPRINT:mgcp:MIN:%3.0lf ';
    $rrd_options .= " GPRINT:mgcp_max:MAX:%3.0lf\\\l ";

    $rrd_options .= ' DEF:sccp=' . $rrd_filename . ':sccp:AVERAGE ';
    $rrd_options .= ' DEF:sccp_max=' . $rrd_filename . ':sccp:MAX';
    $rrd_options .= ' AREA:sccp_max#ddffffdd ';
    $rrd_options .= " LINE1.25:sccp#99ccff:'sccp active calls       ' ";
    $rrd_options .= ' GPRINT:sccp:LAST:%3.0lf ';
    $rrd_options .= ' GPRINT:sccp:MIN:%3.0lf ';
    $rrd_options .= " GPRINT:sccp_max:MAX:%3.0lf\\\l ";

    $rrd_options .= ' DEF:multicast=' . $rrd_filename . ':multicast:AVERAGE ';
    $rrd_options .= ' DEF:multicast_max=' . $rrd_filename . ':multicast:MAX';
    $rrd_options .= ' AREA:multicast_max#ffddffdd ';
    $rrd_options .= " LINE1.25:multicast#cc77ff:'multicast active calls  ' ";
    $rrd_options .= ' GPRINT:multicast:LAST:%3.0lf ';
    $rrd_options .= ' GPRINT:multicast:MIN:%3.0lf ';
    $rrd_options .= " GPRINT:multicast_max:MAX:%3.0lf\\\l ";
}
