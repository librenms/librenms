<?php

require 'includes/html/graphs/common.inc.php';

$rrd_filename = Rrd::name($device['hostname'], 'gaia_firewall_packets');
$rrd_options .= " --vertical-label='Packets per second'";
$rrd_options .= " --lower-limit='0'";

$stats = [
    'accepted'  => '#74C366FF',
    'rejected'  => '#007283FF',
    'dropped'   => '#FFAB00FF',
    'logged'    => '#B1441EFF',
];

$i = 0;
foreach ($stats as $stat => $color) {
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

    $i++;
}

// Add total value
$rrd_options .= " 'CDEF:total=accepted,rejected,dropped,logged,+,+,+'";
$rrd_options .= " 'LINE1:total#000000FF:Total'";
$filler = 16 - strlen('Total');
$current_pad = str_pad('', $filler, ' ', STR_PAD_LEFT);
$rrd_options .= " 'GPRINT:total:LAST:" . $current_pad . "Current\:%8.0lf'";
$rrd_options .= " 'GPRINT:total:AVERAGE:Average\:%8.0lf'";
$rrd_options .= " 'GPRINT:total:MAX:Maximum\:%8.0lf\\n'";
