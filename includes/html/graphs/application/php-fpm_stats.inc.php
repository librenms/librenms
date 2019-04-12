<?php
require 'includes/html/graphs/common.inc.php';
$name = 'php-fpm';
$app_id = $app['app_id'];
$scale_min     = 0;
$colours       = 'mixed';
$unit_text     = '';
$unitlen       = 10;
$bigdescrlen   = 15;
$smalldescrlen = 15;
$dostack       = 0;
$printtotal    = 0;
$addarea       = 1;
$transparency  = 15;

$rrd_filename = rrd_name($device['hostname'], array('app', $name, $app_id));

$array = array(
    'lq' => array('descr' => 'Listen Queue','colour' => '582A72',),
    'mlq' => array('descr' => 'Max Listen Queue','colour' => '28774F',),
    'ip' => array('descr' => 'Idle Procs','colour' => '88CC88',),
    'ap' => array('descr' => 'Active Procs','colour' => 'D46A6A',),
    'tp' => array('descr' => 'Total Procs','colour' => 'FFD1AA',),
    'map' => array('descr' => 'Max Active Procs','colour' => '582A72',),
    'mcr' => array('descr' => 'Max Children Reached','colour' => 'AA5439',),
    'sr' => array('descr' => 'Slow Reqs.','colour' => '28536C',),
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
    echo "file missing: $rrd_filename";
}

require 'includes/html/graphs/generic_v3_multiline.inc.php';
