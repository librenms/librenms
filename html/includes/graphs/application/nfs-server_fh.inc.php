<?php
require 'includes/graphs/common.inc.php';
$scale_min     = 0;
$colours       = 'mixed';
$unit_text     = 'File Handle Stats';
$unitlen       = 15;
$bigdescrlen   = 15;
$smalldescrlen = 15;
$dostack       = 0;
$printtotal    = 0;
$addarea       = 1;
$transparency  = 33;
$rrd_filename  = $config['rrd_dir'].'/'.$device['hostname'].'/app-nfs-server-default-'.$app['app_id'].'.rrd';
$array = array(
    'fh_lookup' => array('descr' => 'fh_lookup'),
    'fh_anon' => array('descr' => 'fh_anon'),
    'fh_ncachedir' => array('descr' => 'fh_ncachedir'),
    'fh_ncachenondir' => array('descr' => 'fh_ncachenondir'),
    'fh_stale' => array('descr' => 'fh_stale'),
);

$i = 0;

if (is_file($rrd_filename)) {
    foreach ($array as $ds => $var) {
        $rrd_list[$i]['filename'] = $rrd_filename;
        $rrd_list[$i]['descr']    = $var['descr'];
        $rrd_list[$i]['ds']       = $ds;
        $rrd_list[$i]['colour']   = $config['graph_colours'][$colours][$i];
        $i++;
    }
} else {
    echo "file missing: $rrd_filename";
}

require 'includes/graphs/generic_v3_multiline.inc.php';

