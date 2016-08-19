<?php
require 'includes/graphs/common.inc.php';
$name = 'os-updates';
$app_id = $app['app_id'];
$scale_min     = 0;
$colours       = 'mixed';
$unit_text     = 'Available updates';
$unitlen       = 18;
$bigdescrlen   = 18;
$smalldescrlen = 18;
$dostack       = 0;
$printtotal    = 0;
$addarea       = 1;
$transparency  = 33;

$rrd_filename = rrd_name($device['hostname'], array('app', $name, $app_id));

$array = array(
    'packages' => array('descr' => 'packages','colour' => '2B9220',),
);

$i = 0;
if (is_file($rrd_filename)) {
    foreach ($array as $ds => $vars) {
        $rrd_list[$i]['filename'] = $rrd_filename;
        $rrd_list[$i]['descr']    = $vars['descr'];
        $rrd_list[$i]['ds']       = $ds;
        $rrd_list[$i]['colour']   = $vars['colour'];
        $i++;
    }
} else {
    echo "file missing: $rrd_filename";
}

require 'includes/graphs/generic_v3_multiline.inc.php';
