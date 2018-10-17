<?php

$rrd_filename = rrd_name($device['hostname'], 'audiocode_IP2Tel');

require 'includes/graphs/common.inc.php';

$stats = array(
'AttemptedCalls'   => '00cc00',
'EstablishedCalls' => '006600',
'BusyCalls'        => 'cc0000',
'NoAnswerCalls'    => '660000',
'NoRouteCalls'     => '0066cc',
'NoMatchCalls'     => '003399',
'FailCalls'        => 'cc00cc',
'FaxAttemptedCalls'=> '990099',
'FaxSuccessCalls'  => '6600cc',
);

$i = 0;

foreach ($stats as $stat => $colour) {
    $i++;
    $rrd_list[$i]['filename'] = $rrd_filename;
    $rrd_list[$i]['descr']    = $stat;
    $rrd_list[$i]['ds']       = $stat;
}

$colours = 'mixed';

$scale_min  = '0';
$nototal    = 1;
$simple_rrd = true;

require 'includes/graphs/generic_multi_line.inc.php';
