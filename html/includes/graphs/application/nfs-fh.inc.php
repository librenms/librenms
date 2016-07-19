<?php
require 'includes/graphs/common.inc.php';
$scale_min     = 0;
$colours       = 'mixed';
$unit_text     = 'Operations';
$unitlen       = 10;
$bigdescrlen   = 15;
$smalldescrlen = 15;
$dostack       = 0;
$printtotal    = 0;
$addarea       = 1;
$transparency  = 33;
$rrd_filename  = $config['rrd_dir'].'/'.$device['hostname'].'/app-nfs-stats-'.$app['app_id'].'.rrd';
$array = array(
    'fh_lookup' => array('descr' => 'lookup','colour' => '136421',),
    'fh_anon' => array('descr' => 'anon','colour' => 'B2C945',),
    'fh_ncachedir' => array('descr' => 'ncachedir','colour' => '778D0D',),
    'fh_ncachenondir' => array('descr' => 'ncachenondir','colour' => '536400',),
    'fh_stale' => array('descr' => 'stale','colour' => '832119',),
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
}
else {
    echo "file missing: $rrd_filename";
}

require 'includes/graphs/generic_multi_line_exact_numbers.inc.php';
