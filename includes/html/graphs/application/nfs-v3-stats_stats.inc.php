<?php
require 'includes/html/graphs/common.inc.php';
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
$rrd_filename  = get_rrd_dir($device['hostname']).'/app-nfs-stats-'.$app['app_id'].'.rrd';
$array         = array(
                 'proc3_null' => array('descr' => 'Null','colour' => '630606',),
                 'proc3_getattr' => array('descr' => 'Get attributes','colour' => '50C150',),
                 'proc3_setattr' => array('descr' => 'Set attributes','colour' => '4D65A2',),
                 'proc3_lookup' => array('descr' => 'Lookup','colour' => '8B64A1',),
                 'proc3_access' => array('descr' => 'Access','colour' => 'AAAA39',),
                 'proc3_read' => array('descr' => 'Read','colour' => '308A30',),
                 'proc3_write' => array('descr' => 'Write','colour' => '457A9A',),
                 'proc3_create' => array('descr' => 'Create','colour' => '690D87',),
                 'proc3_mkdir' => array('descr' => 'Make dir','colour' => '3A3478',),
                 'proc3_mknod' => array('descr' => 'Make nod','colour' => '512E74',),
                 'proc3_link' => array('descr' => 'Link','colour' => '072A3F',),
                 'proc3_remove' => array('descr' => 'Remove','colour' => 'F16464',),
                 'proc3_rmdir' => array('descr' => 'Remove dir','colour' => '57162D',),
                 'proc3_rename' => array('descr' => 'Rename','colour' => 'A40B62',),
                 'proc3_readlink' => array('descr' => 'Read link','colour' => '557917',),
                 'proc3_readdir' => array('descr' => 'Read dir','colour' => 'A3C666',),
                 'proc3_symlink' => array('descr' => 'Symlink','colour' => '85C490',),
                 'proc3_readdirplus' => array('descr' => 'Read dir plus','colour' => 'F1F164',),
                 'proc3_fsstat' => array('descr' => 'FS stat','colour' => 'F1F191',),
                 'proc3_fsinfo' => array('descr' => 'FS info','colour' => '6E2770',),
                 'proc3_pathconf' => array('descr' => 'Pathconf','colour' => '993555',),
                 'proc3_commit' => array('descr' => 'Commit','colour' => '463176',),
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
