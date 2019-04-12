<?php

require 'includes/html/graphs/common.inc.php';

$scale_min    = 0;
$colours      = 'mixed';
$nototal      = 0;
$unit_text    = 'Packets/sec';
$rrd_filename = rrd_name($device['hostname'], array('app', 'powerdns', $app['app_id']));
$array        = array(
                 'rec_questions' => array(
                                     'descr'  => 'Questions',
                                     'colour' => '6699CCFF',
                                    ),
                 'rec_answers'   => array(
                                     'descr'  => 'Answers',
                                     'colour' => '336699FF',
                                    ),
                );

$i = 0;

if (rrdtool_check_rrd_exists($rrd_filename)) {
    foreach ($array as $ds => $var) {
        $rrd_list[$i]['filename'] = $rrd_filename;
        $rrd_list[$i]['descr']    = $var['descr'];
        $rrd_list[$i]['ds']       = $ds;
        $rrd_list[$i]['colour']   = $var['colour'];
        $i++;
    }
} else {
    echo "file missing: $file";
}

require 'includes/html/graphs/generic_multi_line.inc.php';
