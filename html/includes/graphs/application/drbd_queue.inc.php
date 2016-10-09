<?php

require 'includes/graphs/common.inc.php';

$rrd_filename = rrd_name($device['hostname'], array('app', 'drbd', $app['app_instance']));

$array = array(
          'lo' => 'Local I/O',
          'pe' => 'Pending',
          'ua' => 'UnAcked',
          'ap' => 'App Pending',
         );

$i = 0;
if (rrdtool_check_rrd_exists($rrd_filename)) {
    foreach ($array as $ds => $vars) {
        $rrd_list[$i]['filename'] = $rrd_filename;
        if (is_array($vars)) {
            $rrd_list[$i]['descr'] = $vars['descr'];
        } else {
            $rrd_list[$i]['descr'] = $vars;
        }

        $rrd_list[$i]['ds'] = $ds;
        $i++;
    }
} else {
    echo "file missing: $file";
}

$colours   = 'mixed';
$nototal   = 0;
$unit_text = '';

require 'includes/graphs/generic_multi_simplex_seperated.inc.php';
