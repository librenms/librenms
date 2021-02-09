<?php


$rrd_filename = rrd_name($device['hostname'], 'ahradio_wifi0_airtime');
require 'includes/html/graphs/common.inc.php';

// Add tx and rx values and divide by 10,000 to get %.  Could still use Interference Airtime, but there is no OID for this.

$graph_min = "0";
$graph_max = "100";

$rrd_options .= " DEF:wifi0tx=$rrd_filename:wifi0txairtime:AVERAGE";
$rrd_options .= " DEF:wifi0rx=$rrd_filename:wifi0rxairtime:AVERAGE";
$rrd_options .= " CDEF:totalAir=wifi0tx,wifi0rx,+";
$rrd_options .= " CDEF:percentAir=totalAir,10000,/ ";
$rrd_options .= " CDEF:wifi0txpct=wifi0tx,10000,/ ";
$rrd_options .= " CDEF:wifi0rxpct=wifi0rx,10000,/ ";
$rrd_options .= " AREA:percentAir#00FF00:'Total Airtime %          '";
$rrd_options .= " LINE:wifi0txpct#ff0000:'TX Airtime %          '";
$rrd_options .= " LINE:wifi0rxpct#0000ff:'RX Airtime %          '";
