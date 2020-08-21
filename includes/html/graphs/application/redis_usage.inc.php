<?php

require 'includes/html/graphs/common.inc.php';

$scale_min     = 0;
$nototal       = 1;
$unit_text     = 'Usage';
$unitlen       = 15;
$bigdescrlen   = 20;
$smalldescrlen = 15;
$colours       = 'mixed';

$rrd_filename = rrd_name($device['hostname'], array('app', 'redis', $app['app_id'], 'usage'));

$array = array(
          'allocated' => 'Allocated',
          'dataset'   => 'Dataset',
          'lua'   => 'LUA',
          'overhead'   => 'Overhead',
          'peak'   => 'Peak',
          'rss'   => 'RSS',
          'scripts'   => 'Scripts',
          'startup'   => 'Startup',
         );

$rrd_list = array();
if (rrdtool_check_rrd_exists($rrd_filename)) {
    $i = 0;
    foreach ($array as $ds => $descr) {
        $rrd_list[$i]['filename'] = $rrd_filename;
        $rrd_list[$i]['descr']    = $descr;
        $rrd_list[$i]['ds']       = $ds;
        $i++;
    }
} else {
    echo "file missing: $rrd_filename";
}

require 'includes/html/graphs/generic_multi_line_exact_numbers.inc.php';
