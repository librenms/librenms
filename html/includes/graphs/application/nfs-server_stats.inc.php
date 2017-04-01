<?php
require 'includes/graphs/common.inc.php';
$scale_min     = 0;
$colours       = 'mixed';
$unit_text     = 'NFS v3 Operations';
$unitlen       = 10;
$bigdescrlen   = 15;
$smalldescrlen = 15;
$dostack       = 0;
$printtotal    = 0;
$addarea       = 1;
$transparency  = 33;
$rrd_filename  = $config['rrd_dir'].'/'.$device['hostname'].'/app-nfs-server-'.$app['app_id'].'.rrd';
$array         = array(
                 'proc3_null' => array('descr' => 'Null'),
                 'proc3_getattr' => array('descr' => 'Get attributes'),
                 'proc3_setattr' => array('descr' => 'Set attributes'),
                 'proc3_lookup' => array('descr' => 'Lookup'),
                 'proc3_access' => array('descr' => 'Access'),
                 'proc3_read' => array('descr' => 'Read'),
                 'proc3_write' => array('descr' => 'Write'),
                 'proc3_create' => array('descr' => 'Create'),
                 'proc3_mkdir' => array('descr' => 'Make dir'),
                 'proc3_mknod' => array('descr' => 'Make nod'),
                 'proc3_link' => array('descr' => 'Link'),
                 'proc3_remove' => array('descr' => 'Remove'),
                 'proc3_rmdir' => array('descr' => 'Remove dir'),
                 'proc3_rename' => array('descr' => 'Rename'),
                 'proc3_readlink' => array('descr' => 'Read link'),
                 'proc3_readdir' => array('descr' => 'Read dir'),
                 'proc3_symlink' => array('descr' => 'Symlink'),
                 'proc3_readdirplus' => array('descr' => 'Read dir plus'),
                 'proc3_fsstat' => array('descr' => 'FS stat'),
                 'proc3_fsinfo' => array('descr' => 'FS info'),
                 'proc3_pathconf' => array('descr' => 'Pathconf'),
                 'proc3_commit' => array('descr' => 'Commit'),
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
