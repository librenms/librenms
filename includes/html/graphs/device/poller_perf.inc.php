<?php

/*
 * LibreNMS
 *
 * Copyright (c) 2014 Neil Lathwood <https://github.com/laf/ http://www.lathwood.co.uk/fa>
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */
use LibreNMS\Config;

$scale_min = '0';

require 'includes/html/graphs/common.inc.php';

$rrd_filename = Rrd::name($device['hostname'], 'poller-perf');

$rrd_options .= ' DEF:poller=' . $rrd_filename . ':poller:AVERAGE';
$rrd_options .= " 'COMMENT:Seconds      Cur     Min     Max     Avg\\n'";
if (Config::get('applied_site_style') == 'dark') {
    $rrd_options .= ' LINE1.25:poller#63636d:Poller';
} else {
    $rrd_options .= ' LINE1.25:poller#36393d:Poller';
}
$rrd_options .= ' GPRINT:poller:LAST:%6.2lf  GPRINT:poller:MIN:%6.2lf';
$rrd_options .= " GPRINT:poller:MAX:%6.2lf  'GPRINT:poller:AVERAGE:%6.2lf\\n'";

if ($_GET['previous'] == 'yes') {
    $rrd_options .= " COMMENT:' \\n'";
    $rrd_options .= " DEF:pollerX=$rrd_filename:poller:AVERAGE:start=$prev_from:end=$from";
    $rrd_options .= " SHIFT:pollerX:$period";
    $rrd_options .= " LINE1.25:pollerX#CCCCCC:'Prev Poller'\t";
    $rrd_options .= ' GPRINT:pollerX:MIN:%6.2lf';
    $rrd_options .= " GPRINT:pollerX:MAX:%6.2lf  'GPRINT:pollerX:AVERAGE:%6.2lf\\n'";
}
