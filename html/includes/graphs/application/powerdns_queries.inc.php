<?php

require 'includes/html/graphs/common.inc.php';

$scale_min    = 0;
$colours      = 'mixed';
$nototal      = (($width < 224) ? 1 : 0);
$unit_text    = 'Packets/sec';
$rrd_filename = rrd_name($device['hostname'], array('app', 'powerdns', $app['app_id']));
$array        = array(
                 'q_tcpAnswers' => array(
                                    'descr'  => 'TCP Answers',
                                    'colour' => '008800FF',
                                   ),
                 'q_tcpQueries' => array(
                                    'descr'  => 'TCP Queries',
                                    'colour' => '00FF00FF',
                                   ),
                 'q_udpAnswers' => array(
                                    'descr'  => 'UDP Answers',
                                    'colour' => '336699FF',
                                   ),
                 'q_udpQueries' => array(
                                    'descr'  => 'UDP Queries',
                                    'colour' => '6699CCFF',
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

require 'includes/html/graphs/generic_multi_simplex_seperated.inc.php';
