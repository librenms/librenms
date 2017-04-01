<?php
require 'includes/graphs/common.inc.php';
$scale_min     = 0;
$colours       = 'mixed';
$unit_text     = 'RPC Stats';
$unitlen       = 15;
$bigdescrlen   = 15;
$smalldescrlen = 15;
$dostack       = 0;
$printtotal    = 0;
$addarea       = 1;
$transparency  = 33;
$rrd_filename  = $config['rrd_dir'].'/'.$device['hostname'].'/app-nfs-server-'.$app['app_id'].'.rrd';
$array = array(
    'rpc_calls' => array('descr' => 'calls','colour' => '2C8437',), // green : good
    //'rpc_badcalls' => array('descr' => 'bad calls','colour' => '600604',), # this is a sum of nbadfmt, badauth and badclnt
    'rpc_badfmt' => array('descr' => 'bad fmt','colour' => 'E6A4A5',), // pink
    'rpc_badauth' => array('descr' => 'bad auth','colour' => 'B2C8D9',), // blue
    'rpc_badclnt' => array('descr' => 'bad clnt','colour' => 'BEA37A',), // brown
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
