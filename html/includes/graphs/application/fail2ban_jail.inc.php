<?php
require 'includes/graphs/common.inc.php';
$name = 'fail2ban';
$app_id = $app['app_id'];
$scale_min     = 0;
$colours       = 'mixed';
$unit_text     = 'Banned IPs';
$unitlen       = 10;
$bigdescrlen   = 10;
$smalldescrlen = 10;
$dostack       = 0;
$printtotal    = 0;
$addarea       = 1;
$transparency  = 15;

$rrd_filename = rrd_name($device['hostname'], array('app', $name, $app_id, $_GET['jail'] ));

$array = array(
    'banned' => array('descr' => 'Banned','colour' => '582A72',)
);

$i = 0;
if (is_file($rrd_filename)) {
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
