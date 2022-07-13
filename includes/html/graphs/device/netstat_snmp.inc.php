<?php

$rrd_filename = Rrd::name($device['hostname'], 'netstats-snmp');

$stats = [
    'snmpInTraps',
    'snmpOutTraps',
    'snmpInTotalReqVars',
    'snmpInTotalSetVars',
    'snmpOutGetResponses',
    'snmpOutSetRequests',
];

$i = 0;
foreach ($stats as $stat) {
    $i++;
    $rrd_list[$i]['filename'] = $rrd_filename;
    $rrd_list[$i]['descr'] = str_replace('snmp', '', $stat);
    $rrd_list[$i]['ds'] = $stat;
    if (strpos($stat, 'Out') !== false) {
        $rrd_list[$i]['invert'] = true;
    }
}

$colours = 'mixed';

$scale_min = '0';
$nototal = 1;
$simple_rrd = true;

require 'includes/html/graphs/generic_multi.inc.php';
