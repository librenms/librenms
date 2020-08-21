<?php

require 'includes/html/graphs/common.inc.php';

$rrd_filename = rrd_name($device['hostname'], array('app', 'backupninja', $app['app_id']));

$array = array(
          'last_actions' => array(
                     'descr'  => 'last_actions',
                     'colour' => '22FF22',
                    ),
          'last_fatal' => array(
                     'descr'  => 'last_fatal',
                     'colour' => '0022FF',
                    ),
          'last_error'  => array(
                     'descr'  => 'last_error',
                     'colour' => 'FF0000',
                    ),
          'last_warning' => array(
                     'descr'  => 'last_warning',
                     'colour' => '0080C0',
                    ),
         );

$i = 0;
if (rrdtool_check_rrd_exists($rrd_filename)) {
    foreach ($array as $ds => $var) {
        $rrd_list[$i]['filename'] = $rrd_filename;
        $rrd_list[$i]['descr']    = $var['descr'];
        $rrd_list[$i]['ds']       = $ds;
        // $rrd_list[$i]['colour'] = $var['colour'];
        $i++;
    }
} else {
    echo "file missing: $file";
}

$colours   = 'mixed';
$nototal   = 1;
$unit_text = 'backups';

require 'includes/html/graphs/generic_multi_line.inc.php';
