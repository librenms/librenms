<?php

require 'includes/html/graphs/common.inc.php';

$i            = 0;
$scale_min    = 0;
$nototal      = 1;
$unit_text    = 'Query/s';
$rrd_filename = rrd_name($device['hostname'], array('app', 'unbound-operations', $app['app_id']));
$array        = array(
    'opcodeQuery',
    'rcodeNOERROR',
    'rcodeNXDOMAIN',
    'rcodeNodata'
);

$colours      = 'mixed';
$rrd_list     = array();

if (rrdtool_check_rrd_exists($rrd_filename)) {
    foreach ($array as $ds) {
        $rrd_list[$i]['filename'] = $rrd_filename;
        $rrd_list[$i]['descr']    = strtoupper($ds);
        $rrd_list[$i]['ds']       = $ds;
        $i++;
    }
} else {
    echo "file missing: $rrd_filename";
}

require 'includes/html/graphs/generic_multi_line.inc.php';
