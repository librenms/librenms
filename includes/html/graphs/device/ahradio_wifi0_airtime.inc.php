<?php

require 'includes/html/graphs/common.inc.php';

$rrdfilename = rrd_name($device['hostname'], 'ahradio_wifi0_airtime');

// Combine tx and rx values and divide by 10,000 to get %

$scale_min = "0";
$scale_max = "100";

$rrd_options .= " DEF:wifi0tx=$rrd_filename:wifi0txairtime:AVERAGE";
$rrd_options .= " DEF:wifi0rx=$rrd_filename:wifi0rxairtime:AVERAGE";
$rrd_options .= " CDEF:totalAir=wifi0tx,wifi0rx,+";
$rrd_options .= " CDEF:percentAir=totalAir,10000,/ ";
$rrd_options .= " AREA:percentAir#00FF00: 'Total Wifi0 Airtime   '";
