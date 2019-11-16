<?php
$unitlen       = 10;
$bigdescrlen   = 9;
$smalldescrlen = 9;
$dostack       = 0;
$printtotal    = 0;
$unit_text    = 'Events';
$colours      = 'psychedelic';
$rrd_list     = array();

$rrd_filename = rrd_name($device['hostname'], array('app', 'puppet-agent', $app['app_id'], 'events'));
$array        = array(
    'success',
    'failure',
    'total',
);
if (rrdtool_check_rrd_exists($rrd_filename)) {
    foreach ($array as $ds) {
        $rrd_list[]=array(
            'filename' => $rrd_filename,
            'descr' => $ds,
            'ds' => $ds,
        );
    }
} else {
    echo "file missing: $file";
}

require 'includes/html/graphs/generic_multi_line_exact_numbers.inc.php';
