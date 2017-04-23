<?php
require 'includes/graphs/common.inc.php';
$scale_min     = 0;
$unit_text     = 'Job Queu';
$unitlen       = 15;
$bigdescrlen   = 15;
$smalldescrlen = 15;
$dostack       = 0;
$printtotal    = 0;
$addarea       = 1;
$transparency  = 33;
$rrd_filename  = $config['rrd_dir'].'/'.$device['hostname'].'/app-ogs-'.$app['app_id'].'.rrd';

$array = array(
    'running_jobs' => array('descr' => 'running','colour' => '2C8437'), // running : green
    'pending_jobs' => array('descr' => 'pending','colour' => 'E6A4A5'), // pending : pink
    'suspend_jobs' => array('descr' => 'suspended','colour' => 'BEA37A'), // temp. suspended is ok
    'zombie_jobs' => array('descr' => 'zombie','colour' => 'B0262D'), // this sounds bad, but there is a natural occurance
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

require 'includes/graphs/generic_v3_multiline.inc.php';
