<?php

require 'includes/html/graphs/common.inc.php';

$rrdfilename = rrd_name($device['hostname'], 'ahradio_wifi1_airtime');

// Combine tx and rx values and divide by 10,000 to get %

$graph_min = "0";
$graph_max = "100";

$rrd_options .= " DEF:wifi1tx=$rrd_filename:wifi1txairtime:AVERAGE";
$rrd_options .= " DEF:wifi1rx=$rrd_filename:wifi1rxairtime:AVERAGE";
$rrd_options .= " CDEF:totalAir=wifi1tx,wifi1rx,+";
$rrd_options .= " CDEF:percentAir=totalAir,10000,/ ";
$rrd_options .= " AREA:percentAir#00FF00:percentAir";