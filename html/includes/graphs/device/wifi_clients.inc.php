<?php

require 'includes/graphs/common.inc.php';

$scale_min = 0;

$radio1 = rrd_name($device['hostname'], 'wificlients-radio1');
$radio2 = rrd_name($device['hostname'], 'wificlients-radio2');

if (rrdtool_check_rrd_exists($radio1)) {
    $rrd_options .= " COMMENT:'                           Now   Min  Max\\n'";
    $rrd_options .= ' DEF:wificlients1='.$radio1.':wificlients:LAST ';
    $rrd_options .= " LINE1:wificlients1#CC0000:'Clients on Radio1    ' ";
    $rrd_options .= ' GPRINT:wificlients1:LAST:%3.0lf ';
    $rrd_options .= ' GPRINT:wificlients1:MIN:%3.0lf ';
    $rrd_options .= ' GPRINT:wificlients1:MAX:%3.0lf\l ';
    if (rrdtool_check_rrd_exists($radio2)) {
        $rrd_options .= ' DEF:wificlients2='.$radio2.':wificlients:LAST ';
        $rrd_options .= " LINE1:wificlients2#008C00:'Clients on Radio2    ' ";
        $rrd_options .= ' GPRINT:wificlients2:LAST:%3.0lf ';
        $rrd_options .= ' GPRINT:wificlients2:MIN:%3.0lf ';
        $rrd_options .= ' GPRINT:wificlients2:MAX:%3.0lf\l ';
    }
}
