<?php

require 'includes/html/graphs/common.inc.php';

$rrd_filename = Rrd::name($device['hostname'], 'ib_dhcp_messages');
$rrd_options .= " --vertical-label='Messages per minute'";

$stats = [
    'request'   => '#FFAB00FF',
    'ack'       => '#007283FF',
    'discover'  => '#74C366FF',
    'offer'     => '#B1441EFF',
    'inform'    => '#8D85F3FF',
    'nack'      => '#FAFD9EFF',
    'release'   => '#96E78AFF',
    'decline'   => '#FF0000FF',
    'other'     => '#8F9286FF',
];

$i = 0;
foreach ($stats as $stat => $color) {
    $i++;
    $rrd_list[$i]['filename'] = $rrd_filename;
    $rrd_list[$i]['descr'] = ucfirst($stat);
    $rrd_list[$i]['ds'] = $stat;

    // Set up DEFs
    $rrd_options .= ' DEF:' . $stat . '=' . $rrd_filename . ':' . $stat . ':AVERAGE ';

    // Set up CDEFS to multiply with 60 to get per minute value
    $rrd_options .= " 'CDEF:cdef" . $stat . '=' . $stat . ",60,*'";

    // Set up area graphing with stacking
    if ($i == '0') {
        $rrd_options .= " 'AREA:cdef" . $stat . $color . ':' . ucfirst($stat) . "'";
    } else {
        $rrd_options .= " 'AREA:cdef" . $stat . $color . ':' . ucfirst($stat) . ":STACK'";
    }

    // Set up legend, with consistent indent
    $filler = 8 - strlen($stat);
    $current_pad = str_pad('', $filler, ' ', STR_PAD_LEFT);
    $rrd_options .= " 'GPRINT:cdef" . $stat . ':LAST:' . $current_pad . "Current\:%8.0lf'";
    $rrd_options .= " 'GPRINT:cdef" . $stat . ":AVERAGE:Average\:%8.0lf'";
    $rrd_options .= " 'GPRINT:cdef" . $stat . ":MAX:Maximum\:%8.0lf\\n'";
}

// Set up Total value
$rrd_options .= " 'CDEF:cdeftotal=cdefrequest,cdefack,cdefdiscover,cdefoffer,cdefinform,cdefnack,cdefrelease,cdefdecline,cdefother,+,+,+,+,+,+,+,+'";
$rrd_options .= " 'LINE1:cdeftotal#000000FF:Total'";
$filler = 8 - strlen('Total');
$current_pad = str_pad('', $filler, ' ', STR_PAD_LEFT);
$rrd_options .= " 'GPRINT:cdeftotal:LAST:" . $current_pad . "Current\:%8.0lf'";
$rrd_options .= " 'GPRINT:cdeftotal:AVERAGE:Average\:%8.0lf'";
$rrd_options .= " 'GPRINT:cdeftotal:MAX:Maximum\:%8.0lf\\n'";
