<?php

require 'includes/html/graphs/common.inc.php';

$rrd_filename = Rrd::name($device['hostname'], 'ib_dns_performance');
$rrd_options .= " --vertical-label='Answer time in milliseconds'";
$rrd_options .= " --lower-limit='0'";

$stats = [
    'PerfAA'        => '#74C366FF',
    'PerfnonAA'     => '#007283FF',
];

$i = 0;
foreach ($stats as $stat => $color) {
    $i++;
    $rrd_list[$i]['filename'] = $rrd_filename;
    $rrd_list[$i]['descr'] = ucfirst($stat);
    $rrd_list[$i]['ds'] = $stat;

    // Set up DEFs
    $rrd_options .= ' DEF:' . $stat . '=' . $rrd_filename . ':' . $stat . ':AVERAGE ';

    // Set up area graphing with stacking
    if ($i == '0') {
        $rrd_options .= " 'AREA:" . $stat . $color . ':' . $stat . "'";
    } else {
        $rrd_options .= " 'AREA:" . $stat . $color . ':' . $stat . ":STACK'";
    }

    // Set up legend, with consistent indent
    $filler = 15 - strlen($stat);
    $current_pad = str_pad('', $filler, ' ', STR_PAD_LEFT);
    $rrd_options .= " 'GPRINT:" . $stat . ':LAST: ' . $current_pad . "Current\:%8.0lf'";
    $rrd_options .= " 'GPRINT:" . $stat . ":AVERAGE:Average\:%8.0lf'";
    $rrd_options .= " 'GPRINT:" . $stat . ":MAX:Maximum\:%8.0lf\\n'";
}

// Add total value
$rrd_options .= " 'CDEF:cdeftotal=PerfAA,PerfnonAA,+'";
$rrd_options .= " 'LINE1:cdeftotal#000000FF:Total'";
$filler = 16 - strlen('Total');
$current_pad = str_pad('', $filler, ' ', STR_PAD_LEFT);
$rrd_options .= " 'GPRINT:cdeftotal:LAST:" . $current_pad . "Current\:%8.0lf'";
$rrd_options .= " 'GPRINT:cdeftotal:AVERAGE:Average\:%8.0lf'";
$rrd_options .= " 'GPRINT:cdeftotal:MAX:Maximum\:%8.0lf\\n'";
