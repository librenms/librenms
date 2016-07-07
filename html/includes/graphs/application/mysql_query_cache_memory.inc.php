<?php

require 'includes/graphs/common.inc.php';

$rrd_filename = rrd_name($device['hostname'], array('app', 'mysql', $app['app_id']));

$array = array(
          'QCs'   => 'Cache size',
          'QCeFy' => 'Free mem',
         );

$i = 0;
if (is_file($rrd_filename)) {
    foreach ($array as $ds => $vars) {
    $rrd_list[$i]['filename'] = $rrd_filename;
        if (is_array($vars)) {
            $rrd_list[$i]['descr'] = $vars['descr'];
        }
        else {
            $rrd_list[$i]['descr'] = $vars;
        }

        $rrd_list[$i]['ds'] = $ds;
        $i++;
    }
}
else {
    echo "file missing: $file";
}

$colours   = 'mixed';
$nototal   = 1;
$unit_text = 'Bytes';

require 'includes/graphs/generic_multi_simplex_seperated.inc.php';
