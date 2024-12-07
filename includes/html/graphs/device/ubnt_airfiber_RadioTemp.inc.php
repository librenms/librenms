<?php

require 'includes/html/graphs/common.inc.php';

// $rrd_options .= " -l 0 -E ";
$rrdfilename = Rrd::name($device['hostname'], 'ubnt-airfiber-mib');

if (Rrd::checkRrdExists($rrdfilename)) {
    $rrd_options .= " COMMENT:'Celcius               Now     Min      Max\\n'";
    $rrd_options .= ' DEF:radio0TempC=' . $rrdfilename . ':radio0TempC:AVERAGE ';
    $rrd_options .= ' DEF:radio1TempC=' . $rrdfilename . ':radio1TempC:AVERAGE ';
    $rrd_options .= " LINE1:radio0TempC#00FF00:'Radio 0         ' ";
    $rrd_options .= ' GPRINT:radio0TempC:LAST:%0.2lf%s ';
    $rrd_options .= ' GPRINT:radio0TempC:MIN:%0.2lf%s ';
    $rrd_options .= ' GPRINT:radio0TempC:MAX:%0.2lf%s\\\l ';
    $rrd_options .= " LINE1:radio1TempC#CC0000:'Radio 1         ' ";
    $rrd_options .= ' GPRINT:radio1TempC:LAST:%0.2lf%s ';
    $rrd_options .= ' GPRINT:radio1TempC:MIN:%0.2lf%s ';
    $rrd_options .= ' GPRINT:radio1TempC:MAX:%0.2lf%s\\\l ';
}
