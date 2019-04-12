<?php

require 'includes/html/graphs/common.inc.php';

$rrd_filename = rrd_name($device['hostname'], array('app', 'mysql', $app['app_id']));

$array = array(
          'TOC'  => array('descr' => 'Table Cache'),
          'OFs'  => array('descr' => 'Open Files'),
          'OTs'  => array('descr' => 'Open Tables'),
          'OdTs' => array('descr' => 'Opened Tables'),
         );

$i = 0;
if (rrdtool_check_rrd_exists($rrd_filename)) {
    foreach ($array as $ds => $var) {
        $rrd_list[$i]['filename']  = $rrd_filename;
        $rrd_list[$i]['descr'] = $var['descr'];
        $rrd_list[$i]['ds']    = $ds;
        // $rrd_list[$i]['colour'] = $var['colour'];
        $i++;
    }
} else {
    echo "file missing: $file";
}

$colours   = 'mixed';
$nototal   = 1;
$unit_text = '';

require 'includes/html/graphs/generic_multi_simplex_seperated.inc.php';
