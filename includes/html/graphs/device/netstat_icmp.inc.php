<?php

$rrd_filename = Rrd::name($device['hostname'], 'netstats-icmp');

$stats = [
    'icmpInMsgs'      => '00cc00',
    'icmpOutMsgs'     => '006600',
    'icmpInErrors'    => 'cc0000',
    'icmpOutErrors'   => '660000',
    'icmpInEchos'     => '0066cc',
    'icmpOutEchos'    => '003399',
    'icmpInEchoReps'  => 'cc00cc',
    'icmpOutEchoReps' => '990099',
];

$i = 0;

foreach ($stats as $stat => $colour) {
    $i++;
    $rrd_list[$i]['filename'] = $rrd_filename;
    $rrd_list[$i]['descr'] = str_replace('icmp', '', $stat);
    $rrd_list[$i]['ds'] = $stat;
    if (strpos($stat, 'Out') !== false) {
        $rrd_list[$i]['invert'] = true;
    }
}

$colours = 'mixed';

$scale_min = '0';
$nototal = 1;
$simple_rrd = true;

require 'includes/html/graphs/generic_multi_line.inc.php';
