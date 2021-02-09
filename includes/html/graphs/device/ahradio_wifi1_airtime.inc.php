<?php


$rrd_filename = rrd_name($device['hostname'], 'ahradio_wifi1_airtime');
require 'includes/html/graphs/common.inc.php';

// Add tx and rx values and divide by 10,000 to get %.  Could still use Interference Airtime, but there is no OID for this.

$graph_min = "0";
$graph_max = "100";

$rrd_options .= " DEF:wifi1tx=$rrd_filename:wifi1txairtime:AVERAGE";
$rrd_options .= " DEF:wifi1rx=$rrd_filename:wifi1rxairtime:AVERAGE";
$rrd_options .= " CDEF:totalAir=wifi1tx,wifi1rx,+";
$rrd_options .= " CDEF:percentAir=totalAir,10000,/ ";
$rrd_options .= " CDEF:wifi1txpct=wifi1tx,10000,/ ";
$rrd_options .= " CDEF:wifi1rxpct=wifi1rx,10000,/ ";
$rrd_options .= " AREA:percentAir#00FF00:'Total Airtime %          '"; 
$rrd_options .= " LINE:wifi1txpct#ff0000:'TX Airtime %          '";
$rrd_options .= " LINE:wifi1rxpct#0000ff:'RX Airtime %          '";