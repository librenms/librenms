<?php

$rrd_filename = Rrd::name($device['hostname'], 'venturi_subscriber_failures');

require 'includes/html/graphs/common.inc.php';

$i = 0;

foreach ([
    'ClientAuthenticationFailures' => 'ClientAuth',
    'ClientlessAuthenticationFailures' => 'ClientlessAuth',
    'ClientAbortedConnections' => 'ClientAbortedConn',
    'ClientlessAbortedConnections' => 'ClientlessAbortedConn',
    'ClientReassignmentFailures' => 'ClientReassignFail',
    'ClientlessReassignmentFailures' => 'ClientlessReassignFail',
    'ClientStandbyCount' => 'ClientStandby',
    'ClientlessStandbyCount' => 'ClientlessStandby',
    'ClientInactiveCount' => 'ClientInactive',
    'ClientlessInactiveCount' => 'ClientlessInactive',
    'ClientReassignmentCount' => 'ClientReassignment',
    'ClientlessReassignmentCount' => 'ClientlessReassignment',
] as $ds => $descr) {
    $rrd_list[$i]['filename'] = $rrd_filename;
    $rrd_list[$i]['descr'] = $descr;
    $rrd_list[$i]['ds'] = $ds;
    $i++;
}

$unit_text = 'Failures';
$total_units = 'Count';
$colours = 'mega';
$scale_min = '0';

require 'includes/html/graphs/generic_multi_line.inc.php';
