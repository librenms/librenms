<?php

require 'includes/html/graphs/common.inc.php';
$i               = 0;
$scale_min       = 0;
$nototal         = 1;
$descr_len       = 21;
$unit_text       = 'SIP Peers';
$rrd_filename    = rrd_name($device['hostname'], array('app', 'asterisk', 'stats', $app['app_id']));

$astsip_access_array = array(
    'sippeers'        => 'Total Peers',
    'sipmononline'    => 'Online (Monitored)',
    'sipmonoffline'   => 'Offline (Monitored)',
    'sipunmononline'  => 'Online (Unmonitored)',
    'sipunmonoffline' => 'Offline (Unmonitored)'
);

$colours      = 'mixed';
$rrd_list     = array();

if (rrdtool_check_rrd_exists($rrd_filename)) {
    foreach ($astsip_access_array as $ds => $descr) {
        $rrd_list[$i]['filename'] = $rrd_filename;
        $rrd_list[$i]['descr']    = $descr;
        $rrd_list[$i]['ds']       = $ds;
        $i++;
    }
} else {
    echo "file missing: $rrd_filename";
}
require 'includes/html/graphs/generic_multi_line.inc.php';
