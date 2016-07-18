<?php
require 'includes/graphs/common.inc.php';
$scale_min     = 0;
$colours       = 'mixed';
$unit_text     = 'Leases';
$unitlen       = 10;
$bigdescrlen   = 15;
$smalldescrlen = 15;
$dostack       = 0;
$printtotal    = 0;
$addarea       = 1;
$transparency  = 33;
$rrd_filename  = $config['rrd_dir'].'/'.$device['hostname'].'/app-dhcp-stats-'.$app['app_id'].'.rrd';
$array = array(
    'dhcp_total' => array('descr' => 'Total','colour' => '582A72',),
    'dhcp_active' => array('descr' => 'Active','colour' => '28774F',),
    'dhcp_backup' => array('descr' => 'Backup','colour' => 'AA5439',),
    'dhcp_free' => array('descr' => 'Free','colour' => '28536C',),
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
